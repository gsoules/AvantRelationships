<?php
$view = get_view();

$visualizationOptions = array(RelatedItemsGraphView::SHOW_PREVIEW_AT_DEFAULT_LOCATION => __('After metadata elements'), RelatedItemsGraphView::SHOW_PREVIEW_AT_DESIGNATED_LOCATION => __('At designated location'));

$maxRelatedItemsShown = intval(get_option('relationships_max_direct_shown'));
if ($maxRelatedItemsShown == 0)
    $maxRelatedItemsShown = RelatedItemsListView::MAX_RELATED_ITEMS_SHOWN;

$maxIndirectlyRelatedItemsShown = intval(get_option('relationships_max_indirect_shown'));
if ($maxIndirectlyRelatedItemsShown == 0)
    $maxIndirectlyRelatedItemsShown = RelatedItemsListView::MAX_INDIRECTLY_RELATED_ITEMS_SHOWN;

$visualizationOption = intval(get_option('relationships_visualizaton'));
if ($visualizationOption == 0)
    $visualizationOption = RelatedItemsGraphView::SHOW_PREVIEW_AT_DEFAULT_LOCATION;

$deleteTables = intval(get_option('relationships_delete_tables')) != 0;

?>
<div class="field">
    <div class="two columns">
        <label for="relationships_visualizaton"><?php echo __('Visualization Preview'); ?></label>
    </div>
    <div class="inputs five columns">
        <p class="explanation"><?php echo __('Specify where the Relationships Visulization Preview should appear.
        You can designate a location, e.g. in the sidebar, by calling the \'show_relationships_visualization\' hook
        in your theme\'s items/show.php page. To not show the visualization, choose the designated location option,
        but don\'t call the hoook.'); ?></p>
        <?php echo $view->formRadio('relationships_visualizaton', $visualizationOption, null, $visualizationOptions); ?>
    </div>
</div>

<div class="field">
    <div class="two columns">
        <label for="relationships_max_direct_shown"><?php echo __('Max direct items'); ?></label>
    </div>
    <div class="inputs five columns">
        <p class="explanation"><?php echo __("Number of directly related items listed before displaying a \"Show more\" message."); ?></p>
        <?php echo $view->formText('relationships_max_direct_shown', $maxRelatedItemsShown); ?>
    </div>
</div>

<div class="field">
    <div class="two columns">
        <label for="relationships_max_indirect_shown"><?php echo __('Max indirect items'); ?></label>
    </div>
    <div class="inputs five columns">
        <p class="explanation"><?php echo __("Number of indirectly related items listed before displaying a \"Show more\" message."); ?></p>
        <?php echo $view->formText('relationships_max_indirect_shown', $maxIndirectlyRelatedItemsShown); ?>
    </div>
</div>

<div class="field">
    <div class="two columns">
        <label><?php echo __('Delete Tables'); ?></label>
    </div>
    <div class="inputs five columns">
        <p class="explanation"><?php echo __(" WARNING: Checking the box below will cause all relationship database
        tables and data to be permanently deleted if you uninstall this plugin. Do not check this box unless you are
        certain that in the future you will not be using relationship data that you created (relationships, types,
        rules, and cover images) while using this plugin . If you are just experimenting with the plugin, leave the
        box unchecked. If you decide not to use the plugin, check the box, Save Changes, and then uninstall the plugin."); ?></p>
        <?php echo $view->formCheckbox('relationships_delete_tables', true, array('checked' => $deleteTables)); ?>
    </div>
</div>





