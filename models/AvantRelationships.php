<?php
class AvantRelationships
{
    public static function createCustomRelationshipTreeNodes($item, $tree)
    {
        $nodes = array();
        $customCallbacks = RelationshipsConfig::getOptionDataForCustomRelationships();
        foreach ($customCallbacks as $callback)
        {
            $className = $callback['class'];
            $functionName = $callback['function'];
            $callbackFunctionName = "$className::$functionName";
            if (is_callable($callbackFunctionName))
            {
                $nodes = array_merge($nodes, call_user_func($callbackFunctionName, $item, $tree));
            }
        }
        return $nodes;
    }

    public static function emitImplicitRelationshipLink($text, $sourceItemId)
    {
        $text = $text ? html_entity_decode($text) : "";
        $results = ItemMetadata::getItemsWithElementValue(ItemMetadata::getTitleElementId(), $text);

        if (empty($results))
            return $text;

        $foundRelatedItem = false;

        foreach ($results as $result)
        {
            $targetItemId = $result['id'];
            $targetItem = ItemMetadata::getItemFromId($targetItemId);

            if (empty($targetItem))
            {
                // The user does not have access to the target item e.g. because it's private.
                continue;
            }

            if (ItemMetadata::getElementTextForElementName($targetItem, 'Type') != 'Reference')
            {
                // An implicitly related item must be a Reference.
                continue;
            }

            if ($sourceItemId == $targetItemId)
            {
                // This item is its own creator.
                continue;
            }

            $foundRelatedItem = true;
            break;
        }

        if (!$foundRelatedItem)
            return $text;

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