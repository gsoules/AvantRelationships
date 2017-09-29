<?php
$pageTitle = __('Edit Relationship Types');
echo head(array('title' => $pageTitle, 'bodyclass' => 'edit-relationship-types'));

$db = get_db();
$list = $db->getTable('RelationshipTypes')->getRelationshipTypes();

echo '<div>' . __('Drag Relationship Types in the list below into the preferred order. Then click the Update Order button at the bottom of the page.') . '<br/>' .
    __('The number on the left is the Id for the Relationship Type. Use this Id when specifying Directives.') . '<br/>' .
    __('The number on the right indicates how many relationships use the type. Only types with zero usage can be removed.') . '</div>';

?>
<br/>

<ul id="relationship-items-list" class="ui-sortable">
    <?php
    foreach ($list as $type)
    {
        $id = $type['id'];
        $usageCount = RelationshipTypesEditor::getUsageCount($id);
        $removeClass = $usageCount > 0 || count($list) == 1 ? ' no-remove' : '';

        $sourceRule = get_view()->formSelect(null, $type['source_rule_id'], array('multiple' => false, 'class' => 'source-rule-id'), get_table_options('RelationshipRules'));
        $targetRule = get_view()->formSelect(null, $type['target_rule_id'], array('multiple' => false, 'class' => 'target-rule-id'), get_table_options('RelationshipRules'));
    ?>
        <li id="<?php echo $id; ?>">
            <div class="main_link ui-sortable-handle">
                <div class="sortable-item">
                    <div class="relationship-type-title"><?php echo $type['id']; ?></div>
                    <div class="relationship-type-title"><?php echo $type['source_name']; ?></div>
                    <div class="relationship-type-title"><?php echo $type['target_name']; ?></div>
                    <span class="relationship-item-count"><?php echo $usageCount; ?></span>
                    <span class="drawer"></span>
                </div>
                <div class="drawer-contents" style="display:none;">
                    <div class="order" style="display: none"><?php echo $type['order']; ?></div>
                    <table>
                        <tr>
                            <td colspan="2">
                                <label><?php echo __('Source Rule'); ?></label><?php echo $sourceRule; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo __('Source Name'); ?></label><input class="source-name" type="text" value="<?php echo $type['source_name']; ?>">
                            </td>
                            <td>
                                <label><?php echo __('Source Label'); ?></label><input class="source-label" type="text" value="<?php echo $type['source_label']; ?>">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label><?php echo __('Target Rule'); ?></label><?php echo $targetRule; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo __('Target Name'); ?></label><input class="target-name" type="text" value="<?php echo $type['target_name']; ?>">
                            </td>
                            <td>
                                <label><?php echo __('Target Label'); ?></label><input class="target-label" type="text" value="<?php echo $type['target_label']; ?>">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label><?php echo __('Directives'); ?></label><input class="directives" type="text" value="<?php echo html_escape($type['directives']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <label><?php echo __('Ancestry'); ?></label><input class="ancestry" type="text" value="<?php echo html_escape($type['ancestry']); ?>">
                            </td>
                        </tr>
                    </table>

                    <button type="button" class="action-button update-item-button"><?php echo __('Update'); ?></button>
                    <button type="button" class="action-button remove-item-button red button<?php echo $removeClass; ?>"><?php echo __('Remove'); ?></button>
                </div>
            </div>
        </li>
        <?php
    }
    ?>
</ul>

<button type="button" class="action-button add-item-button">Add Relationship Type</button>
<button type="button" class="action-button update-order-button">Update Order</button>
<p id="message-area"></p>

<?php echo get_view()->partial('/edit-relationship-types-script.php'); ?>

<?php echo foot(); ?>
