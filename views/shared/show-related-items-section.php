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

$showRows = intval(get_option(RelationshipsConfig::OPTION_SHOW_RELATED_ITEMS_AS_ROWS))== 1;

echo "<li class='$sectionClass'>";
echo "<p class='related-items-section-name'>{$sectionTreeNode->getName()}</p>";
echo $showRows ? "<div class='related-item-rows'>" : "<ul class='item-preview'>";
foreach ($kids as $kid)
{
    /* @var $relatedItem RelatedItem */
    $relatedItem = $kid->getRelatedItem();
    $item = $relatedItem->getItem();

    $extraClass = " class='$itemId-$sectionId-extra' style='display:none'";
    $attributes = $itemsShown <= $maxItemsVisible - 1 ? '' : $extraClass;
    if ($showRows)
    {
        $rowText = ItemMetadata::getItemTitle($item);
        echo "<div{$attributes}>$rowText</div>";
    }
    else
    {
        $itemPreview = new ItemPreview($item);
        echo $itemPreview->emitItemPreviewAsListElement($relatedItem->usesCoverImage(), $attributes);
    }

    $itemsShown++;
    if ($itemsShown >= $maxItemsToEmit)
    {
        break;
    }
};
echo $showRows ? "</div>" : "</ul>";
echo "</li>";

echo $showMoreLink;
