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

    public function emitSection(RelatedItemsTreeNode $directKid, $excludeItem = null)
    {
        $itemsToShow = intval(get_option('relationships_max_direct_shown'));
        if ($itemsToShow <= 0)
            $itemsToShow = self::MAX_RELATED_ITEMS_SHOWN;

        return $this->emitRelatedItemsSection($directKid, 'related-items-main-section', $itemsToShow, $excludeItem);
    }

    public function emitSubsection(RelatedItemsTreeNode $indirectKid)
    {
        $itemsToShow = intval(get_option('relationships_max_indirect_shown'));
        if ($itemsToShow <= 0)
            $itemsToShow = self::MAX_INDIRECTLY_RELATED_ITEMS_SHOWN;

        return $this->emitRelatedItemsSection($indirectKid, 'related-items-subsection', $itemsToShow);
    }

    public function emitSubsectionHeader($subsectionName)
    {
        return "<p class=\"related-items-section-name related-items-subsection-header\">$subsectionName</p>";
    }

    protected function emitRelatedItemsSection($sectionTreeNode, $class, $maxItemsVisible, $excludeItem = null)
    {
        return $this->view->partial('show-related-items-section.php', array(
            'relatedItemsListView' => $this,
            'sectionTreeNode' => $sectionTreeNode,
            'sectionClass' => $class,
            'maxItemsVisible' => $maxItemsVisible,
            'excludeItem' => $excludeItem));
    }

    public function emitRelatedItemsList($relatedItemsModel, $excludeItem = null)
    {
        return $this->view->partial('show-related-items-list.php', array(
            'relatedItemsListView' => $this,
            'relatedItemsModel' => $relatedItemsModel,
            'excludeItem' => $excludeItem));
    }
}