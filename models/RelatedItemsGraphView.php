<?php

class RelatedItemsGraphView
{
    const SHOW_PREVIEW_AT_DEFAULT_LOCATION = 0;
    const SHOW_PREVIEW_AT_DESIGNATED_LOCATION = 1;

    protected $relatedItemsModel;
    protected $view;

    public function __construct($view, RelatedItemsModel $relatedItemsModel)
    {
        $this->view = $view;
        $this->relatedItemsModel = $relatedItemsModel;
    }

    public function emitRelatedItemsGraph()
    {
        $relatedItemsGraph = $this->relatedItemsModel->getRelatedItemsGraph();

        if ($relatedItemsGraph->getEdgeCount() == 0)
        {
            // Don't show a graph that has only one node.
            return '';
        }

        return $this->view->partial('show-related-items-graph.php', array(
            'graphData' => $relatedItemsGraph->getGraphData()));
    }
}