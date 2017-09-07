<?php

class RelatedItemsModel
{
    protected $primaryItem;
    protected $relatedItemsGraph;
    protected $relatedItemsGraphView;
    protected $relatedItemsTree;
    protected $relatedItemsListView;
    protected $view;

    public function __construct($primaryItem, $view = null)
    {
        $this->primaryItem = $primaryItem;
        $this->view = $view;
    }

    public function emitRelatedItemsGraphView()
    {
        if (!isset($this->relatedItemsGraphView))
        {
            $this->relatedItemsGraphView = new RelatedItemsGraphView($this->view, $this);
        }
        return $this->relatedItemsGraphView->emitRelatedItemsGraph();
    }

    public function emitRelatedItemsListView($excludeItem = null)
    {
        if (!isset($this->relatedItemsListView))
        {
            $this->relatedItemsListView = new RelatedItemsListView($this->view);
        }
        return $this->relatedItemsListView->emitRelatedItemsList($this, $excludeItem);
    }

    public function getPrimaryItem()
    {
        return $this->primaryItem;
    }

    public function getRelatedItems()
    {
        $relatedItemsTree = $this->getRelatedItemsTree();
        return $relatedItemsTree->getRelatedItems();
    }


    public function getRelatedItemsGraph()
    {
        if (!isset($this->relatedItemsGraph))
        {
            $this->relatedItemsGraph = new RelatedItemsGraph($this);
        }
        return $this->relatedItemsGraph;
    }

    public function getRelatedItemsTree()
    {
        if (!isset($this->relatedItemsTree))
        {
            $this->relatedItemsTree = new RelatedItemsTree($this->primaryItem);
        }
        return $this->relatedItemsTree;
    }
}