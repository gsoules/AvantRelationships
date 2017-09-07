<?php
// Check if the cover image identifier exists in the REQUEST. If it does, the Save failed due to an error on one
// of the Edit tabs, possibly due to an invalid cover image identifier. In the error case, display whatever value
// is the input field. When the REQUEST is empty, get the value from the database.
$coverImageIdentifier = isset($_REQUEST['cover-image-identifier']) ? $_REQUEST['cover-image-identifier'] : '';

if (empty($coverImageIdentifier))
    $coverImageIdentifier = ItemView::getCoverImageIdentifier($item->id);

echo '<p>' . __('Specify the Identifier for the item to use for this item\'s cover image.') . '</p>';
echo '<p><field>' . __('Item:') . ' </field>';
echo get_view()->formText('cover-image-identifier', $coverImageIdentifier, array('size' => 4));
echo "</p>";

if (!empty($coverImageIdentifier))
{
    $coverImageItem = ItemView::getItemFromIdentifier($coverImageIdentifier);
    if (!empty($coverImageItem))
    {
        $itemView = new ItemView($coverImageItem);
        $html = "<div class=\"item-preview cover-image\">";
        $html .= $itemView->emitItemThumbnail(false);
        $html .= $itemView->emitItemTitle();
        $html .= "</div>";
        echo $html;
    }
}
