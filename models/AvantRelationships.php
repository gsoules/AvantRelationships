<?php
class AvantRelationships
{
    public static function emitImplicitRelationshipLink($text, $sourceItemId)
    {
        $text = html_entity_decode($text);
        $result = ItemSearch::getFirstItemWithElementValue(ItemMetadata::getTitleElementId(), $text);

        if (empty($result))
            return $text;

        $targetItemId = $result['id'];
        $targetItem = ItemMetadata::getItemFromId($targetItemId);
        if (empty($targetItem))
        {
            // The user does not have access to the target item e.g. because it's private.
            return $text;
        }

        if ($sourceItemId == $targetItemId)
        {
            // This item is its own creator.
            return $text;
        }

        $tooltip = "See item for \"$text\"";
        $href = html_escape(url("items/show/$targetItemId"));
        return "<a href='$href' title='$tooltip'>$text</a>";
    }

    public static function saveConfiguration()
    {
        $maxDirectItems = intval($_POST['avantrelationships_max_direct_shown']);
        $maxIndirectItems = intval($_POST['avantrelationships_max_indirect_shown']);

        if ($maxDirectItems == 0)
        {
            throw new Omeka_Validate_Exception(__('Max Direct Items must be an integer greater than zero.'));
        }

        if ($maxIndirectItems == 0)
        {
            throw new Omeka_Validate_Exception(__('Max Indirect Items must be an integer greater than zero.'));
        }

        set_option('avantrelationships_max_direct_shown', $maxDirectItems);
        set_option('avantrelationships_max_indirect_shown', $maxIndirectItems);
        set_option('avantrelationships_visualizaton', $_POST['avantrelationships_visualizaton']);
        set_option('avantrelationships_delete_tables', (int)(boolean)$_POST['avantrelationships_delete_tables']);
    }
}