<?php
$primaryItemIdentifier = ItemView::getItemIdentifier($item);
?>
<div><?php echo ItemView::getItemTitle($item); ?></div>
<br/>
<div>This item is: <?php echo $primaryItemIdentifier; ?></div>
<br/>
<table>
    <thead>
    <tr>
        <th><?php echo __('Relationship'); ?></th>
        <th><?php echo __('Related Item'); ?></th>
        <th><?php echo __('Related Item Title'); ?></th>
        <th><?php echo __('Action'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($relatedItems as $relatedItem)
    {
        $relatedItemIdentifier = $relatedItem->getIdentifier();
        ?>
        <tr id="<?php echo $relatedItem->getRelationshipId(); ?>">
            <td><?php echo $relatedItem->getRelationshipName(); ?></td>
            <td><?php echo $relatedItemIdentifier; ?></td>
            <td><?php echo RelatedItemsEditor::getRelatedItemLink($relatedItemIdentifier) ?></td>
            <td>
                <button type="button" class="action-button edit-relationship-button"><?php echo __('Edit'); ?></button>
                <button type="button" class="action-button remove-relationship-button red button"><?php echo __('Remove'); ?></button>
            </td>
        </tr>
    <?php }; ?>
    <tr class="add-relationship-row">
        <td><?php echo get_view()->formSelect('relationship-type-code', null, array('multiple' => false), $formSelectRelationshipNames); ?></td>
        <td><?php echo get_view()->formText('related-item-identifier', null, array('size' => 4)); ?></td>
        <td></td>
        <td>
            <button type="button" class="action-button add-relationship-button"><?php echo __('Add'); ?></button>
            <button type="button" class="action-button edit-relationship-button"><?php echo __('Edit'); ?></button>
            <button type="button" class="action-button remove-relationship-button red button"><?php echo __('Remove'); ?></button>
        </td>
    </tr>
    </tbody>
</table>

<?php echo get_view()->partial('/edit-relationships-script.php', array('primaryItemIdentifier' => $primaryItemIdentifier)); ?>


