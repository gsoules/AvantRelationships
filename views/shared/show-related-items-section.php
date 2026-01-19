<?php

$sectionKids = $sectionTreeNode->getKids();
$sectionId = $sectionTreeNode->getId();

$kids = array();

if (empty($excludeItem))
{
    $kids = $sectionKids;
}
else
{
    // Create a copy of the section tree node kids minus the excluded item. The excluded item is not empty when it's
    // image is being displayed as the item's cover image and therefore should not appear again as a related item.
    foreach ($sectionKids as $sectionKid)
    {
        $relatedItem = $sectionKid->getRelatedItem();
        $item = $relatedItem->getItem();
        if ($item->id == $excludeItem->id)
            continue;
        $kids[] = $sectionKid;
    }

    if (empty($kids))
    {
        // The only item in the section is being excluded so don't emit the section.
        return;
    }
}

$numItemsInSection = count($kids);

$itemsShown = 0;
$showMoreLink = '';
$maxItemsToEmit = $numItemsInSection;
$showMoreThreshold = 10;

if ($numItemsInSection > $maxItemsVisible)
{
    if (($numItemsInSection - $maxItemsVisible) < $showMoreThreshold)
    {
        // Don't emit a Show More link when there are not that many more items to show.
        $maxItemsVisible = $numItemsInSection;
    }
    else
    {
        // Only show a small number of thumbnails and provide a button to show more.
        $numMore = $numItemsInSection - $maxItemsVisible;
        $maxSearchItemsToShow = $maxItemsVisible * 5;
        $advancedSearchUrl = $sectionTreeNode->getData();

        if (!empty($advancedSearchUrl) && $numItemsInSection > $maxSearchItemsToShow)
        {
            // There are too many items to show. Emit a link to do an advanced search to find all the items.
            $maxItemsToEmit = $maxItemsVisible;
            $showMoreLink = "<div class='related-items-see-all'><a href='$advancedSearchUrl' id='{$sectionTreeNode->getId()}'>See all $numItemsInSection items</a></div>";
        }
        else
        {
            // Emit a button to toggle show more / show less.
            $showMoreLink = "<div class='related-items-show-more'><a href='#' id='$itemId-$sectionId'>" . __('Show %s more', $numMore) . '</a></div>';
        }
    }
}

if (plugin_is_active('MDIBL'))
{
    // Always show a link that goes to Table view in Layout 3 sorted by report number. Layout 3 must be defined in AvantElements config.
    $advancedSearchUrl = $sectionTreeNode->getData();
    $advancedSearchUrl .= "&sort=Report&view=1&layout=3";

    // Get a description of what's being shown, reports or documents.
    $kid = reset($kids);
    $relatedItem = $kid->getRelatedItem();
    $label = $relatedItem->getRelationshipLabelPlural();
    $label = strtolower($label);

    $showMoreLink = "<div class='related-items-see-all'><a href='$advancedSearchUrl' id='{$sectionTreeNode->getId()}'>Show a table of all $numItemsInSection $label</a></div>";
}

// Determine if related items should be shown as previews (normal behavior) or as compact rows.
// Even if the user opted for rows, show as a preview if any of the related items have a file attachment.
$showRelatedItemsAsRows = intval(get_option(RelationshipsConfig::OPTION_SHOW_RELATED_ITEMS_AS_ROWS))== 1;
if ($showRelatedItemsAsRows)
{
    foreach ($kids as $kid)
    {
        $relatedItem = $kid->getRelatedItem();
        $item = $relatedItem->getItem();
        if ($item->Files)
        {
            $showRelatedItemsAsRows = false;
            break;
        }
    }
}

echo "<li class='$sectionClass'>";
echo "<p class='related-items-section-name'>{$sectionTreeNode->getName()}</p>";
echo $showRelatedItemsAsRows ? "<div class='related-item-rows'>" : "<ul class='item-preview'>";
foreach ($kids as $kid)
{
    /* @var $relatedItem RelatedItem */
    $relatedItem = $kid->getRelatedItem();
    $item = $relatedItem->getItem();

    $class = '';
    $style = '';
    $attributes = '';
    if ($itemsShown > $maxItemsVisible - 1)
    {
        $class = "$itemId-$sectionId-extra";
        $style = 'display:none;';
    }
    if ($showRelatedItemsAsRows)
    {
        $class .= ' related-item-row';
    }
    if (!empty($class))
        $attributes = " class='$class'";
    if (!empty($style))
        $attributes .= " style='$style'";

    $itemPreview = new ItemPreview($item);
    if ($showRelatedItemsAsRows)
    {
        echo $itemPreview->emitItemPreviewAsRow($attributes);
    }
    else
    {
        echo $itemPreview->emitItemPreviewAsListElement($relatedItem->usesCoverImage(), $attributes);
    }

    $itemsShown++;
    if ($itemsShown >= $maxItemsToEmit)
    {
        break;
    }
};
echo $showRelatedItemsAsRows ? "</div>" : "</ul>";
echo "</li>";

echo $showMoreLink;
