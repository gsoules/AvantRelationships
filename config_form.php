<?php
$view = get_view();

// If this page posted back with an error, get the invalid option values, otherwise get the options from the database.
$maxRelatedItemsShown = isset($_POST[RelationshipsConfig::OPTION_MAX_DIRECT_ITEMS]) ? $_POST[RelationshipsConfig::OPTION_MAX_DIRECT_ITEMS] : get_option(RelationshipsConfig::OPTION_MAX_DIRECT_ITEMS);
$maxIndirectlyRelatedItemsShown = isset($_POST[RelationshipsConfig::OPTION_MAX_INDIRECT_ITEMS]) ? $_POST[RelationshipsConfig::OPTION_MAX_INDIRECT_ITEMS] : get_option(RelationshipsConfig::OPTION_MAX_INDIRECT_ITEMS);

$visualizationOptions = array(RelatedItemsGraphView::SHOW_PREVIEW_AT_DEFAULT_LOCATION => __('After metadata elements'),
    RelatedItemsGraphView::SHOW_PREVIEW_AT_DESIGNATED_LOCATION => __('At designated location'),
    RelatedItemsGraphView::SHOW_PREVIEW_NEVER => __('Don\'t show visualization'));

$visualizationOption = intval(get_option(RelationshipsConfig::OPTION_VISUALIZATION));

$implicitRelationships = RelationshipsConfig::getOptionTextForImplicitRelationships();
$implicitRelationshipsRows = max(2, count(explode(PHP_EOL, $implicitRelationships)));

$customRelationships = RelationshipsConfig::getOptionTextForCustomRelationships();
$customRelationshipsRows = max(2, count(explode(PHP_EOL, $customRelationships)));

$showRelatedItemsAsRows = intval(get_option(RelationshipsConfig::OPTION_SHOW_RELATED_ITEMS_AS_ROWS)) != 0;

$deleteTables = intval(get_option(RelationshipsConfig::OPTION_DELETE_TABLES)) != 0;

?>
<div class="plugin-help">
    <a href="https://github.com/gsoules/AvantRelationships#usage" target="_blank">Learn about the configuration options on this page</a>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_VISUALIZATION_PREVIEW; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Specify where the Relationships Visulization Preview appears.'); ?></p>
        <?php echo $view->formRadio(RelationshipsConfig::OPTION_VISUALIZATION, $visualizationOption, null, $visualizationOptions); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_MAX_DIRECT_ITEMS; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Number of directly related items listed before displaying a \"Show more\" message."); ?></p>
        <?php echo $view->formText(RelationshipsConfig::OPTION_MAX_DIRECT_ITEMS, $maxRelatedItemsShown, array('style' => 'width: 40px;')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_MAX_INDIRECT_ITEMS; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Number of indirectly related items listed before displaying a \"Show more\" message."); ?></p>
        <?php echo $view->formText(RelationshipsConfig::OPTION_MAX_INDIRECT_ITEMS, $maxIndirectlyRelatedItemsShown, array('style' => 'width: 40px;')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_IMPLICIT_RELATIONSHIPS; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that are implicitly related to Titles."); ?></p>
        <?php echo $view->formTextarea(RelationshipsConfig::OPTION_IMPLICIT_RELATIONSHIPS, $implicitRelationships, array('rows' => $implicitRelationshipsRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_CUSTOM_RELATIONSHIPS; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Callback functions to provide custom relationships."); ?></p>
        <?php echo $view->formTextarea(RelationshipsConfig::OPTION_CUSTOM_RELATIONSHIPS, $customRelationships, array('rows' => $customRelationshipsRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_SHOW_RELATED_ITEMS_AS_ROWS; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Show related items as rows instead of as image thumbnails."); ?></p>
        <?php echo $view->formCheckbox(RelationshipsConfig::OPTION_SHOW_RELATED_ITEMS_AS_ROWS, true, array('checked' => $showRelatedItemsAsRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_DELETE_TABLES; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __(" WARNING: Checking this box will cause all relationship data to be
        permanently deleted if you uninstall this plugin.<br/>
        Click <a href=\"https://github.com/gsoules/AvantRelationships#usage\" target=\"_blank\" style=\"color:red;\">
        here</a> to read the documentation for the Delete Tables option before unchecking the box."); ?></p>
        <?php echo $view->formCheckbox(RelationshipsConfig::OPTION_DELETE_TABLES, true, array('checked' => $deleteTables)); ?>
    </div>
</div>





