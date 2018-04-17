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
        <p class="explanation"><?php echo __('Specify where the Relationships Visulization Preview appears.'); ?></p>
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
        <p class="explanation"><?php echo __(" WARNING: Checking this box will cause all relationship data to be
        permanently deleted if you uninstall this plugin.<br/>
        Click <a href=\"https://github.com/gsoules/AvantRelationships#usage\" target=\"_blank\" style=\"color:red;\">
        here</a> to read the documentation for the Delete Tables option before unchecking the box."); ?></p>
        <?php echo $view->formCheckbox('avantrelationships_delete_tables', true, array('checked' => $deleteTables)); ?>
    </div>
</div>





