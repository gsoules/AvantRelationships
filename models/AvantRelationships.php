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
        return "<a class='metadata-implicit-link' href='$href' title='$tooltip'>$text</a>";
    }

    public static function initializeImplicitRelationshipFilters(&$filters)
    {
        $implicitRelationshipsData = RelationshipsConfig::getOptionDataForImplicitRelationships();
        foreach ($implicitRelationshipsData as $elementId => $definition)
        {
            $elementName = $definition['name'];
            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            if (!empty($elementSetName))
            {
                // Set up a call to be made when this element is displayed on a Show page.
                $filters['filterRelationshipImplicit' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
            }
        }
    }
}