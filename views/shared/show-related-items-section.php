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

if ($numItemsInSection > $maxItemsVisible)
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
        $showMoreLink = "<div class='related-items-show-more'><a href='#' id='{$sectionTreeNode->getId()}'>" . __('Show %s more', $numMore) . '</a></div>';
    }
}
?>
<li class="<?php echo $sectionClass; ?>">
    <p class="related-items-section-name"><?php echo $sectionTreeNode->getName(); ?></p>
    <ul class="item-preview">
        <?php
        foreach ($kids as $kid)
        {
            /* @var $relatedItem RelatedItem */
            $relatedItem = $kid->getRelatedItem();
            $item = $relatedItem->getItem();
            $itemView = new ItemView($item);
            $attributes = $itemsShown <= $maxItemsVisible - 1 ? '' : " class='$sectionId-extra' style='display:none'";
            echo $itemView->emitItemPreviewAsListElement($relatedItem->usesCoverImage(), $attributes);
            $itemsShown++;
            if ($itemsShown >= $maxItemsToEmit)
            {
                break;
            }
        };
        ?>
    </ul>
</li>

<?php echo $showMoreLink; ?>
