<?php

class AvantRelationshipsPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $relatedItemsEditor;
    protected $relatedItemsModel;

    protected $_hooks = array(
        'admin_head',
        'admin_items_show',
        'admin_items_search',
        'after_delete_record',
        'after_save_item',
        'before_save_item',
        'define_routes',
        'config',
        'config_form',
        'install',
        'initialize',
        'items_browse_sql',
        'public_head',
        'public_items_show',
        'show_relationships_visualization',
        'uninstall',
        'upgrade'
    );

    protected $_filters = array(
        'admin_items_form_tabs',
        'admin_navigation_main',
        'item_search_filters',
        'related_items_model'
    );

    protected function createRelatedItemsEditor($primaryItem = null)
    {
        if (!isset($this->relatedItemsEditor))
        {
            $this->createRelatedItemsModel($primaryItem);
            $this->relatedItemsEditor = new RelatedItemsEditor($this->relatedItemsModel, $primaryItem);
        }
    }

    protected function createRelatedItemsModel($primaryItem, $view = null)
    {
        if (!isset($this->relatedItemsModel) || $this->relatedItemsModel->getPrimaryItem()->id != $primaryItem->id)
        {
            $this->relatedItemsModel = new RelatedItemsModel($primaryItem, $view);
        }
    }

    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Relationships'),
            'uri' => url('relationships/browse')
        );
        return $nav;
    }

    public function filterAdminItemsFormTabs($tabs, $args)
    {
        // Emit the Relationships tab content on the admin/items/edit page. The following variables are declared and
        // initialized here and used in related-items-edit-form.php: $item, $relatedItems, $formSelectRelationshipNames.
        $item = $args['item'];

        if (empty($item->id))
        {
            // This must be an item that is being added, but has not yet been saved.
            // Return the tabs without adding one for Relationships since you can't
            // create relationships for an item that does not yet exist.
            return $tabs;
        }

        $this->createRelatedItemsModel($item);
        $relatedItems = $this->relatedItemsModel->getRelatedItems();

        $this->createRelatedItemsEditor($item);
        $formSelectRelationshipNames = $this->relatedItemsEditor->getRelationshipNamesSelectList();

        ob_start();
        include 'relationships-editor.php';
        $content = ob_get_contents();
        ob_end_clean();

        $tabs[__('Relationships')] = $content;

        ob_start();
        include 'cover-image-editor.php';
        $content = ob_get_contents();
        ob_end_clean();

        $tabs[__('Cover Image')] = $content;

        return $tabs;
    }

    public function filterItemSearchFilters($displayArray, $args)
    {
        $this->createRelatedItemsEditor();
        $filters = $this->relatedItemsEditor->getSearchFilters($args);
        if (!empty($filters))
            $displayArray['Relationships']= $this->relatedItemsEditor->getSearchFilters($args);
        return $displayArray;
    }

    public function filterRelatedItemsModel($relatedItemGroups, $args)
    {
        $this->createRelatedItemsModel($args['item'], $args['view']);
        return $this->relatedItemsModel;
    }

    protected function head()
    {
        queue_css_file('magnific-popup');
        queue_css_file('cytoscape.js-panzoom');
        queue_css_file('related-items-graph');

        queue_js_file('jquery.magnific-popup.min');
        queue_js_file('cytoscape.min');
        queue_js_file('cytoscape-cose-bilkent');
        queue_js_file('dagre');
        queue_js_file('cytoscape-dagre');
        queue_js_file('cytoscape-panzoom');
    }

    public function hookAdminHead($args)
    {
        $this->head();
    }

    public function hookAdminItemsSearch()
    {
        $relationshipNames = get_table_options('RelationshipTypes');
        echo common('related-items-advanced-search', array('formSelectRelationshipName' => $relationshipNames));
    }

    public function hookAfterDeleteRecord($args)
    {
        $this->createRelatedItemsEditor($args['record']);
        $this->relatedItemsEditor->afterDeleteItem($args);
    }

    public function hookAdminItemsShow($args)
    {
        $this->hookPublicItemsShow($args);
    }

    public function hookAfterSaveItem($args)
    {
        $coverImageIdentifier = isset($_REQUEST['cover-image-identifier']) ? $_REQUEST['cover-image-identifier'] : '';
        $this->createRelatedItemsEditor($args['record']);
        $this->relatedItemsEditor->afterSaveItem($args, $coverImageIdentifier);
    }

    public function hookBeforeSaveItem($args)
    {
        $this->createRelatedItemsEditor($args['record']);
        $this->relatedItemsEditor->beforeSaveItem($args);
    }

    public function hookConfig()
    {
        set_option('relationships_max_direct_shown', $_POST['relationships_max_direct_shown']);
        set_option('relationships_max_indirect_shown', $_POST['relationships_max_indirect_shown']);
        set_option('relationships_visualizaton', $_POST['relationships_visualizaton']);
        set_option('relationships_delete_tables', (int)(boolean)$_POST['relationships_delete_tables']);
    }

    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }

    public function hookDefineRoutes($args)
    {
        $args['router']->addConfig(new Zend_Config_Ini(
            dirname(__FILE__) . DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
    }

    public function hookInitialize()
    {
        return;
    }

    public function hookInstall() {

        RelationshipsTableFactory::CreateRelationshipRulesTable();
        RelationshipsTableFactory::CreateRelationshipTypesTable();
        RelationshipsTableFactory::CreateRelationshipsTable();
        RelationshipsTableFactory::CreateRelationshipImagesTable();
        RelationshipsTableFactory::CreateDefaultRelationshipTypesAndRules();
    }

    public function hookItemsBrowseSql($args)
    {
        $params = $args['params'];
        $controller = isset($params['controller']) ? $params['controller'] : '';
        $action = isset($params['action']) ? $params['action'] : '';
        $admin = isset($params['admin']) ? $params['admin'] : false;

        if (!($admin && $controller == 'items' && $action == 'browse'))
        {
            // This SQL is not for an advanced search -- ignore it.
            return;
        }

        $this->createRelatedItemsEditor();
        $this->relatedItemsEditor->processAdvancedSearchSql($args);
    }

    public function hookPublicHead($args)
    {
        $this->head();
    }

    public function hookPublicItemsShow($args)
    {
        $visualizationOption = intval(get_option('relationships_visualizaton'));
        $excludeItem = isset($args['exclude']) ? $args['exclude'] : null;
        $this->createRelatedItemsModel($args['item'], $args['view']);

        // Create the HTML for the List View. Always do this before creating the HTML for the graph view
        // because the graph view logic alters the relationships tree.
        $listViewHtml = $this->relatedItemsModel->emitRelatedItemsListView($excludeItem);

        if ($visualizationOption == RelatedItemsGraphView::SHOW_PREVIEW_AT_DEFAULT_LOCATION)
        {
            echo $this->relatedItemsModel->emitRelatedItemsGraphView($excludeItem);
        }

        echo $listViewHtml;
    }

    public function hookShowRelationshipsVisualization($args)
    {
        $visualizationOption = intval(get_option('relationships_visualizaton'));
        if ($visualizationOption == RelatedItemsGraphView::SHOW_PREVIEW_AT_DESIGNATED_LOCATION)
        {
            $this->createRelatedItemsModel($args['item'], $args['view']);
            echo $this->relatedItemsModel->emitRelatedItemsGraphView();
        }
    }

    public function hookUninstall()
    {
        $deleteTables = intval(get_option('relationships_delete_tables'))== 1;
        if (!$deleteTables)
            return;

        RelationshipsTableFactory::DropRelatonshipsTable();
        RelationshipsTableFactory::DropRelatonshipTypesTable();
        RelationshipsTableFactory::DropRelatonshipRulesTable();
        RelationshipsTableFactory::DropRelatonshipImagesTable();
    }

    public function hookUpgrade($args)
    {
        return;
    }
}
