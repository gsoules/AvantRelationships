<?php
$pageTitle = __('Edit Relationship Rules');
echo head(array('title' => $pageTitle, 'bodyclass' => 'edit-relationship-rules'));

$db = get_db();
$list = $db->getTable('RelationshipRules')->getRelationshipRules();

echo '<div>' . __('The number following the rule indicates how many relationship types use the rule. Only rules with zero usage can be removed.') . '</div>';
?>
<br/>

<ul id="relationship-items-list" class="ui-sortable">
    <?php
    foreach ($list as $rule)
    {
        $id = $rule['id'];
        $usageCount = RelationshipRulesEditor::getUsageCount($id);
        $removeClass = $usageCount > 0 || count($list) == 1 ? ' no-remove' : '';
        ?>
        <li id="<?php echo $id; ?>">
            <div class="main_link ui-sortable-handle">
                <div class="sortable-item not-sortable">
                    <div class="relationship-rule-title"><?php echo __('Rule ') . $rule['id']; ?>: <?php echo $rule['description']; ?></div>
                    <span class="relationship-item-count"><?php echo $usageCount; ?></span>
                    <span class="drawer"></span>
                </div>
                <div class="drawer-contents" style="display:none;">
                    <label><?php echo __('Description'); ?></label><input class="description" type="text" value="<?php echo $rule['description']; ?>">
                    <label><?php echo __('Rule'); ?></label><input class="rule" type="text" value="<?php echo $rule['rule']; ?>">
                    <button type="button" class="action-button update-item-button"><?php echo __('Update'); ?></button>
                    <button type="button" class="action-button remove-item-button red button<?php echo $removeClass; ?>"><?php echo __('Remove'); ?></button>
                </div>
            </div>
        </li>
        <?php
    }
    ?>
</ul>

<button type="button" class="action-button add-item-button"><?php echo __('Add Relationship Rule'); ?></button>

<?php echo get_view()->partial('/edit-relationship-rules-script.php'); ?>

<?php echo foot(); ?>
