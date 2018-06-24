<?php

class RelatedItemsListView
{
    const MAX_RELATED_ITEMS_SHOWN = 6;
    const MAX_INDIRECTLY_RELATED_ITEMS_SHOWN = 8;

    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function emitSection(RelatedItemsTreeNode $directKid, $itemId, $excludeItem = null)
    {
        $itemsToShow = intval(get_option(RelationshipsConfig::OPTION_MAX_INDIRECT_ITEMS));
        if ($itemsToShow <= 0)
            $itemsToShow = self::MAX_RELATED_ITEMS_SHOWN;

        return $this->emitRelatedItemsSection($directKid, 'related-items-main-section', $itemsToShow, $itemId, $excludeItem);
    }

    public function emitSubsection(RelatedItemsTreeNode $indirectKid, $itemId)
    {
        $itemsToShow = intval(get_option(RelationshipsConfig::OPTION_MAX_INDIRECT_ITEMS));
        if ($itemsToShow <= 0)
            $itemsToShow = self::MAX_INDIRECTLY_RELATED_ITEMS_SHOWN;

        return $this->emitRelatedItemsSection($indirectKid, 'related-items-subsection', $itemsToShow, $itemId);
    }

    public function emitSubsectionHeader($subsectionName)
    {
        return "<p class=\"related-items-section-name related-items-subsection-header\">$subsectionName</p>";
    }

    protected function emitRelatedItemsSection($sectionTreeNode, $class, $maxItemsVisible, $itemId, $excludeItem = null)
    {
        return $this->view->partial('show-related-items-section.php', array(
            'relatedItemsListView' => $this,
            'sectionTreeNode' => $sectionTreeNode,
            'sectionClass' => $class,
            'maxItemsVisible' => $maxItemsVisible,
            'itemId' => $itemId,
            'excludeItem' => $excludeItem));
    }

    public function emitRelatedItemsList($relatedItemsModel, $listViewIndex, $itemId, $excludeItem = null)
    {
        $html = $this->view->partial('show-related-items-list.php', array(
            'relatedItemsListView' => $this,
            'relatedItemsModel' => $relatedItemsModel,
            'itemId' => $itemId,
            'excludeItem' => $excludeItem));

        if ($listViewIndex == 1)
        {
            // Emit the jQuery for the Show More button, but only emit it once.
            $html .= $this->view->partial('show-more-items-script.php');
        }

        return $html;
    }
}