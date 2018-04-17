<?php
$view = get_view();

// If this page posted back with an error, get the invalid option values, otherwise get the options from the database.
$maxRelatedItemsShown = isset($_POST['avantrelationships_max_direct_shown']) ? $_POST['avantrelationships_max_direct_shown'] : get_option('avantrelationships_max_direct_shown');
$maxIndirectlyRelatedItemsShown = isset($_POST['avantrelationships_max_indirect_shown']) ? $_POST['avantrelationships_max_indirect_shown'] : get_option('avantrelationships_max_indirect_shown');

$visualizationOptions = array(RelatedItemsGraphView::SHOW_PREVIEW_AT_DEFAULT_LOCATION => __('After metadata elements'),
    RelatedItemsGraphView::SHOW_PREVIEW_AT_DESIGNATED_LOCATION => __('At designated location'),
    RelatedItemsGraphView::SHOW_PREVIEW_NEVER => __('Don\'t show visualization'));

$visualizationOption = intval(get_option('avantrelationships_visualizaton'));
$deleteTables = intval(get_option('avantrelationships_delete_tables')) != 0;

?>
<div class="plugin-help">
    <a href="https://github.com/gsoules/AvantRelationships#usage" target="_blank">Learn about the configuration options on this page</a>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantrelationships_visualizaton"><?php echo __('Visualization Preview'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Specify where the Relationships Visulization Preview should appear.
        You can designate a location, e.g. in the sidebar, by calling the \'show_relationships_visualization\' hook
        in your theme\'s items/show.php page. To not show the visualization, choose the designated location option,
        but don\'t call the hoook.'); ?></p>
        <?php echo $view->formRadio('avantrelationships_visualizaton', $visualizationOption, null, $visualizationOptions); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantrelationships_max_direct_shown"><?php echo __('Max Direct Items'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Number of directly related items listed before displaying a \"Show more\" message."); ?></p>
        <?php echo $view->formText('avantrelationships_max_direct_shown', $maxRelatedItemsShown, array('style' => 'width: 40px;')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="avantrelationships_max_indirect_shown"><?php echo __('Max Indirect Items'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Number of indirectly related items listed before displaying a \"Show more\" message."); ?></p>
        <?php echo $view->formText('avantrelationships_max_indirect_shown', $maxIndirectlyRelatedItemsShown, array('style' => 'width: 40px;')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Delete Tables'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __(" WARNING: Checking the box below will cause all relationship database
        tables and data to be permanently deleted if you uninstall this plugin. Do not check this box unless you are
        certain that in the future you will not be using relationship data that you created (relationships, types,
        rules, and cover images) while using this plugin . If you are just experimenting with the plugin, leave the
        box unchecked. If you decide not to use the plugin, check the box, Save Changes, and then uninstall the plugin."); ?></p>
        <?php echo $view->formCheckbox('avantrelationships_delete_tables', true, array('checked' => $deleteTables)); ?>
    </div>
</div>





