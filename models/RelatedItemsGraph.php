<?php

class RelatedItemsGraph
{
    protected $datasets = array();
    protected $datasetsJavascript;
    protected $edgeCount;
    protected $elements = array();
    protected $elementsJavascript;
    protected $layoutsJavascript;
    protected $layoutsMap = array();
    protected $maxExpanderNodeKids;
    protected $maxPrimaryNodeKids;
    protected $primaryItem;
    protected $graphRootNode;
    protected $relatedItemsModel;
    protected $relatedItemsTree;
    protected $rootDataset;

    const LAYOUT_COSE = 1;
    const LAYOUT_CONCENTRIC = 2;
    const LAYOUT_DAGR_TB = 3;
    const LAYOUT_DAGR_LR = 4;

    public function __construct(RelatedItemsModel $relatedItemsModel)
    {
        $this->edgeCount = 0;
        $this->maxExpanderNodeKids = 7;
        $this->maxPrimaryNodeKids = 8;

        $this->relatedItemsModel = $relatedItemsModel;
        $this->primaryItem = $relatedItemsModel->getPrimaryItem();

        $this->createGraph();
    }

    protected function addLayout($id, $options)
    {
        $this->layoutsJavascript .= "layouts[$id] = $options;\r";
    }

    protected function addLayouts()
    {
        $padding = 15;
        $this->addLayout(self::LAYOUT_COSE, "{name:'cose-bilkent', idealEdgeLength:100, padding:$padding}");
        $this->addLayout(self::LAYOUT_CONCENTRIC, "{name:'concentric', nodeDimensionsIncludeLabels:true, padding:$padding}");
        $this->addLayout(self::LAYOUT_DAGR_TB, "{name:'dagre', rankDir:'TB', rankSep:100, padding:$padding}");
        $this->addLayout(self::LAYOUT_DAGR_LR, "{name:'dagre', rankDir:'LR', rankSep:100, padding:$padding}");
    }

    protected function chooseLayoutForExpanderDataset(RelatedItemsTreeNode $node)
    {
        $nodeCount = $node->getKidCount();

        if ($nodeCount <= 2)
            $layout = self::LAYOUT_DAGR_LR;
        elseif ($nodeCount <= 6)
            $layout = self::LAYOUT_COSE;
        else
            $layout = self::LAYOUT_CONCENTRIC;

        $this->layoutsMap["'{$node->getId()}'"] = $layout;
    }

    protected function chooseLayoutForRootNodeDataset($rootNodeDatasetId)
    {
        $nodeCount = $this->graphRootNode->getKidCount();

        if ($nodeCount <= 2)
            $layout = self::LAYOUT_DAGR_LR;
        elseif ($nodeCount <= 5)
            $layout = self::LAYOUT_DAGR_TB;
        elseif ($nodeCount <= 9)
            $layout = self::LAYOUT_COSE;
        else
            $layout = self::LAYOUT_CONCENTRIC;

        $this->layoutsMap[$rootNodeDatasetId] = $layout;
    }

    protected function createDatasetsJavascript()
    {
        $this->datasetsJavascript .= "var datasets = [];\r";
        foreach ($this->datasets as $key => $dataset)
        {
            $this->datasetsJavascript .= "datasets[$key] = [$dataset];\r";
        }
        $this->datasetsJavascript .= "var rootDatasetId = '{$this->graphRootNode->getId()}';\r";
    }

    protected function createEdgeFromParentToNode(RelatedItemsTreeNode $node)
    {
        $parentNode = $node->getParent();
        if (!empty($parentNode))
        {
            // Create an edge to this node from its parent node.
            $sourceId = $parentNode->getId();
            $targetId = $node->getId();
            $edgeId = $this->deriveEdgeId($sourceId, $targetId);
            $class = '';

            if ($node->hasRelatedItem())
            {
                if ($this->isExpanderNode($parentNode))
                {
                    // Don't label edges from an expander node to its kids. The label is not needed
                    // because the edge from the expander's parent to the expander is labeled to
                    // describe the expander's kids.
                    $name = '';
                    $class = ", classes:'expanderEdge'";
                }
                else
                {
                    $name = $node->getRelatedItem()->getRelationshipLabelSingular();
                }
            }
            else
            {
                $name = $node->getName();
            }

            $label = empty($name) ? '' : ", label:'$name'";
            $this->elements["'$edgeId'"] = "{data:{id:'$edgeId', source:'$sourceId', target:'$targetId'$label}$class}";
            $this->edgeCount++;
        }
    }

    protected function createElements(RelatedItemsTreeNode $node)
    {
        $this->elements["'{$node->getId()}'"] = "{$node->getData()}";

        $this->createEdgeFromParentToNode($node);

        $kids = $node->getKids();
        foreach ($kids as $kid)
        {
            $this->createElements($kid);
        }
    }

    protected function createElementsJavascript()
    {
        $this->elementsJavascript = "var elements = [];\r";
        foreach ($this->elements as $key => $element)
        {
            $this->elementsJavascript .= "elements[$key] = $element;\r";
        }
    }

    protected function createExpanderNodeDatasets(RelatedItemsTreeNode $node)
    {
        // Create a sets of graph elements, one for each expander element, that will be displayed when
        // the user clicks an expander node to see its kids. Each set contains the expander node, its
        // kid nodes, and the path from the expander up to the root node.
        if ($this->isExpanderNode($node))
        {
            // Get the set of nodes and edges from the root node to this node.
            $dataset = $this->createPathFromRootToExpander($node->getParent(), $node);

            // Add each of the expander node's kids and edges to the dataset.
            $kids = $node->getKids();
            $sourceId = $node->getId();
            foreach ($kids as $kid)
            {
                $targetId = $kid->getId();
                $edgeId = $this->deriveEdgeId($sourceId, $targetId);;
                $dataset .= "'$targetId',";
                $dataset .= "'$edgeId',";
            }

            $this->datasets["'{$node->getId()}'"] = $dataset;

            $this->chooseLayoutForExpanderDataset($node);
        }
        else
        {
            $kids = $node->getKids();
            foreach ($kids as $kid)
            {
                $this->createExpanderNodeDatasets($kid);
            }
        }
    }

    protected function createGraph()
    {
        // Get the tree containing the data that the graph will represent.
        $this->relatedItemsTree = $this->relatedItemsModel->getRelatedItemsTree();
        $this->graphRootNode = $this->relatedItemsTree->getRootNode();

        // Reshape the tree by pruning away nodes that are not necessary for the graph.
        $this->optimizeTree();

        // Walk the pruned tree and decorate each node with data the graphing package needs to render that node.
        $this->decorateTreeWithGraphElements($this->graphRootNode);

        // Remove excess kids from nodes. This is done to keep the graph from getting too cluttered.
        $this->pruneExcessKids($this->graphRootNode);

        // Create the graph's node and edge elements.
        $this->createElements($this->graphRootNode);
        $this->createElementsJavascript();

        // Create sets of graph elements that will display when the graph first loads.
        $this->createRootNodeDatasetAndLayout();

        // Create sets of graph elements that will display when the user clicks on expander nodes to show child nodes.
        $this->createExpanderNodeDatasets($this->graphRootNode);

        // Create arrays that determine which dataset to use when displaying the root graph or any specific expander node.
        $this->createDatasetsJavascript();

        // Create arrays that determine which layout to use when displaying the root graph or any specific expander node.
        $this->createLayoutsJavascript();
    }

    protected function createLayoutsJavascript()
    {
        $this->layoutsJavascript = "var layouts = [];\r";
        $this->addLayouts();
        $this->layoutsJavascript .= "var layoutMap = [];\r";
        foreach ($this->layoutsMap as $key => $mapping)
        {
            $this->layoutsJavascript .= "layoutMap[$key] = $mapping;\r";
        }
    }

    protected function createPathFromRootToExpander($parentNode, $childNode)
    {
        // This method walks up the tree from $childNode to the root node. During the walk it
        // records each edge and node element along the path. It then reverses the elements
        // so that it emits the path in order starting at the root, being sure to first emit
        // a node before an edge that connects to that node (the graph library ignores edges
        // that reference a non-existent node even if the node is added to the graph later).
        $path = '';
        $elements = array();
        while (!empty($parentNode))
        {
            $sourceId = $parentNode->getId();
            $targetId = $childNode->getId();
            $edgeId = $this->deriveEdgeId($sourceId, $targetId);;
            $elements[] = "'$edgeId',";
            $elements[] = "'$targetId',";
            $childNode = $parentNode;
            $parentNode = $parentNode->getParent();
        }
        $path .= "'{$childNode->getId()}',";
        $elements = array_reverse($elements);
        foreach ($elements as $element)
        {
            $path .= $element;
        }
        return $path;
    }

    protected function createRootDataset(RelatedItemsTreeNode $node, $depth, $maxDepth)
    {
        // Create the set of elements that will appear when the graph initially loads and again when
        // the user contracts an expander node so that the graph returns to its initial state.
        $this->rootDataset .= "'{$node->getId()}',";

        $parentNode = $node->getParent();
        if (!empty($parentNode))
        {
            // Add the edge Id to the path.
            $sourceId = $parentNode->getId();
            $targetId = $node->getId();
            $edgeId = $this->deriveEdgeId($sourceId, $targetId);;
            $this->rootDataset .= "'$edgeId',";
        }

        if ($depth < $maxDepth)
        {
            $depth++;
            $kids = $node->getKids();
            foreach ($kids as $kid)
            {
                $this->createRootDataset($kid, $depth, $maxDepth);
            }
        }
    }

    protected function createRootNodeDatasetAndLayout()
    {
        $rootDatasetDepth = $this->relatedItemsTree->hasIndirectlyRelatedItems() ? 3 : 2;
        $this->rootDataset = '';
        $this->createRootDataset($this->graphRootNode, 1, $rootDatasetDepth);
        $rootNodeDatasetId = "'{$this->graphRootNode->getId()}'";
        $this->datasets[$rootNodeDatasetId] = $this->rootDataset;

        $this->chooseLayoutForRootNodeDataset($rootNodeDatasetId);
    }

    protected function decorateTreeWithGraphElements(RelatedItemsTreeNode $node)
    {
        // Walk the entire related items tree and decorate each node with the data elements that
        // the graph library needs to display each nodes, it's name, and it's href if it has one.
        $style = '';
        $class = '';
        $href = '';

        $name = $this->purifyNodeName($node->getName());

        if ($node->hasRelatedItem())
        {
            /* @var $relatedItem RelatedItem */
            $relatedItem = $node->getRelatedItem();
            $item = $relatedItem->getItem();

            $url = $this->getBackgroundImageUrl($item, $relatedItem->usesCoverImage());
            $style = ", style:{'background-image':'$url'}";

            $url = url("items/show/{$item->id}");
            $href = ", href:'$url'";
        }
        elseif ($node->getId() == $this->graphRootNode->getId())
        {
            // The root node doesn't have a RelatedItem, so use its item.
            $url = $this->getBackgroundImageUrl($this->primaryItem, false);
            $style = ", style:{'background-image':'$url'}";

            $class = ", classes:'root'";
            $identifier = ItemView::getItemIdentifier($this->primaryItem);
            $name .= " [$identifier]";
        }
        else
        {
            $class = ", classes:'expander'";
            $kidCount = $node->getKidCount();
            $name = $kidCount > $this->maxExpanderNodeKids ? "{$this->maxExpanderNodeKids}+" : $kidCount;
        }

        $id = $node->getId();
        $node->setData("{data:{id:'$id', name:'$name'{$href}}{$class}{$style}}");

        $kids = $node->getKids();
        foreach ($kids as $kid)
        {
            $this->decorateTreeWithGraphElements($kid);
        }
    }

    protected function deriveEdgeId($sourceId, $targetId)
    {
        return "e-$sourceId-$targetId";
    }

    protected function flattenIndirectNodes()
    {
        // Remove nodes that don't need to appear in the graph. These are nodes that are necessary to create a
        // well-formed tree, but can be pruned away to make the tree more pleasing visually e.g. by elevating
        // kids to their parent's level and removing the parent.
        $kids = $this->graphRootNode->getKids();
        foreach ($kids as $directKid)
        {
            $indirectKids = $directKid->getKids();
            foreach ($indirectKids as $indirectKid)
            {
                $indirectKid->moveToGrandparent();
            }
            $directKid->removeFromParent();
        }
        unset($directKid);
    }

    protected function flattenSingleKidExpanderNodes(RelatedItemsTreeNode $node)
    {
        // Prune away expander nodes that have only one kid by replacing the expander node with the kid node.
        if ($node->isLeaf())
        {
            /* @var $parent RelatedItemsTreeNode */
            $parent = $node->getParent();
            if (!empty($parent))
            {
                $parentKidCount = $parent->getKidCount();
                if (!$parent->hasRelatedItem() && $parentKidCount == 1)
                {
                    $node->moveToGrandparent();
                    $parent->removeFromParent();
                    unset($parent);
                }
            }
        }
        else
        {
            $kids = $node->getKids();
            foreach ($kids as $kid)
            {
                $this->flattenSingleKidExpanderNodes($kid);
            }
        }
    }

    protected function optimizeTree()
    {
        if ($this->relatedItemsTree->hasIndirectlyRelatedItems())
        {
            $this->flattenIndirectNodes();
        }

        $this->flattenSingleKidExpanderNodes($this->graphRootNode);
    }

    protected function getBackgroundImageUrl($item, $useCoverImage = true)
    {
        // Determine which image to use as a graph node's background.
        $coverImageItem = ItemView::getCoverImageItem($item);
        if (!$coverImageItem || !$useCoverImage)
        {
            // Use the displayed item's image when there is no cover image item or when the plugin is
            // configured to not show the cover image for the target item of this relationship.
            $coverImageItem = $item;
        }

        return ItemView::getItemImageUri($coverImageItem);
    }

    public function getEdgeCount()
    {
        return $this->edgeCount;
    }

    public function getGraphData()
    {
        return $this->elementsJavascript . $this->datasetsJavascript . $this->layoutsJavascript;
    }

    protected function isExpanderNode(RelatedItemsTreeNode $node)
    {
        // An expander node has kids, but has no data of its own.
        return $node->getKidCount() > 0 && !$node->hasRelatedItem() && $node->getId() != $this->graphRootNode->getId();
    }

    protected function pruneExcessKids(RelatedItemsTreeNode $node)
    {
        // Find nodes that have more kids than can be comfortably displayed and prune away excess kids.
        $kidCount = $node->getKidCount();

        if ($this->isExpanderNode($node))
        {
            if ($kidCount > $this->maxExpanderNodeKids)
            {
                $this->pruneKids($node, $this->maxExpanderNodeKids);
            }
        }
        else
        {
            if ($node == $this->graphRootNode)
            {
                $extras = $kidCount - $this->maxPrimaryNodeKids;
                if ($extras >= 1)
                {
                    // Adjust the max so that there are at least two orphans because telling the user that
                    // 1 item is not showing looks bad since you could have shown the item in the area
                    // where it says 1 item is not showing.
                    $maxKids = $extras == 1 ? $this->maxPrimaryNodeKids - 1 : $this->maxPrimaryNodeKids;
                    $this->pruneKids($node, $maxKids);
                }
            }

            $kids = $node->getKids();
            foreach ($kids as $kid)
            {
                $this->pruneExcessKids($kid);
            }
        }
    }

    protected function pruneKids(RelatedItemsTreeNode $node, $maxKids)
    {
        $orphans = array();
        $count = 0;

        $kids = $node->getKids();
        foreach ($kids as $kid)
        {
            $count++;
            if ($count > $maxKids)
            {
                $orphans[] = $kid;
            }
        }

        $orphanCount = count($orphans);
        if ($orphanCount >= 1)
        {
            $id = $orphans[0]->getId();

            foreach ($orphans as $orphan)
            {
                /* @var $orphan RelatedItemsTreeNode */
                $orphan->removeFromParent();
                unset($orphan);
            }

            if ($node == $this->graphRootNode)
            {
                // Replace the set of orphaned nodes with a text node that says how many nodes are not showing.
                $moreNode = new RelatedItemsTreeNode($id, '');
                $message = __('%s related items are not shown', $orphanCount);
                $moreNode->setData("{data:{id:'$id', name:'$message'}, classes:'moreNode'}");
                $node->addKid($moreNode);
            }
        }
    }

    protected function purifyNodeName($name)
    {
        // Remove anything from a node name that would cause it to become an unterminated Javascript string
        // or contain unwanted HTML tags. The problems this method addresses are:
        //   1. A user inadvertently puts a newline after a title which introduces a carriage return
        //      which in turn results in Omeka giving us a BR tag. A carriage return will cause an
        //      unterminated string and the BR tag looks ugly.
        //   2. The string contains quotes as HTML entities. This method turns those into plain
        //      single quotes and also escape them with a backslash.
        //   3. There may be other cases that have not surfaced yet..

        // Convert and escape single quotes
        $text = html_entity_decode($name, ENT_QUOTES);
        $text = str_replace("'", "\'", $text);

        // Remove carriage returns and line feeds
        $text = str_replace(array("\r", "\n"), '', $text);
        $text = str_replace("<br />", '', $text);
        return $text;
    }
}
