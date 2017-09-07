<?php
ini_set('max_execution_time', 300);

$pageTitle = __('Validate Relationships');
echo head(array('title' => $pageTitle, 'bodyclass' => 'validate-relationships'));

echo '<h4>' . __('Relationships Validator') . '</h4>';

$db = get_db();
$relationships = $db->getTable('Relationships')->getRelationshipsList();

$invalidCount = 0;
$totalCount = count($relationships);

echo '<p>' . __('Checked %s relationships', $totalCount) . '</p>';

foreach ($relationships as $index => $relationship)
{
    $id = $relationship->id;
    $relationshipTypeCode = RelationshipTypeCode::createRelationshipTypeCode(
        RelationshipTypeCode::SOURCE_TO_TARGET,
        $relationship->relationship_type_id);
    $sourceItem = ItemView::getItemFromId($relationship->source_item_id);
    $targetItem = ItemView::getItemFromId($relationship->target_item_id);

    $relatedItemsEditor = new RelatedItemsEditor(null, $sourceItem);
    $valid = $relatedItemsEditor->validateRelationship($sourceItem, $relationshipTypeCode, $targetItem);
    if (!$valid)
    {
        $invalidCount++;
        $errorMessage = $relatedItemsEditor->getValidationErrorMessage();
        $sourceIdentifier = ItemView::getItemIdentifier($sourceItem);
        $targetIdentifier = ItemView::getItemIdentifier($targetItem);
        $adminSourceUrl = WEB_ROOT . '/admin/items/edit/' . $sourceItem->id;
        $adminTargetUrl = WEB_ROOT . '/admin/items/edit/' . $targetItem->id;
        $sourceLink = "<a href='$adminSourceUrl' target='_blank'>$sourceIdentifier</a>";
        $targetLink = "<a href='$adminTargetUrl' target='_blank'>$targetIdentifier</a>";
        $errorReport = "<div>$invalidCount / $index - " . __('This item: %s', $sourceLink) . '&nbsp;&nbsp;&nbsp;&nbsp;' . __('Related item: %s', $targetLink) . "</div><div>$errorMessage</div>";
        echo "<p>$errorReport</p>\r\n";
    }
}

echo '<p>' . __('%s invalid relationship(s) found', $invalidCount) . '</p>';

echo foot();
?>
