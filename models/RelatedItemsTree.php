<?php

class RelatedItemsTree
{
    const MAX_ANCESTRY_LEVELS = 25;

    protected $db;
    protected $hasIndirectlyRelatedItems;
    protected $kidId;
    protected $primaryItem;
    protected $rootNode;

    protected $ancestries = array();
    protected $relatedItems = array();
    protected $relationshipGroups = array();

    public function __construct($primaryItem)
    {
        $this->db = get_db();
        $this->primaryItem = $primaryItem;
        $this->hasIndirectlyRelatedItems = false;

        $name = ItemMetadata::getElementTextFromElementId($primaryItem, ItemMetadata::getTitleElementId());
        $this->rootNode = $this->createKid($name);

        $this->createTreeFromRelatedItemGroups();
        $this->insertImplicitRelationships();
    }

    protected function addAncestor($name, RelatedItem $relatedItem)
    {
        $this->addAncestryGroup('2-ancestors', $name, $relatedItem);
    }

    protected function addAncestryGroup($kind, $name, RelatedItem $relatedItem)
    {
        $ancestryId = 'ancestry-' . $relatedItem->getRelationshipTypeId();
        $relatedItemId = $relatedItem->getItemId();
        $names = explode(',', $name);
        $namePlural = $names[0];
        $nameSingular = $names[1];
        $relatedItem->setLabels($namePlural, $nameSingular);
        $this->ancestries[$ancestryId][$kind][$namePlural][$relatedItemId] = $relatedItem;
    }

    protected function addAncestryGroupsPlaceholder(RelatedItem $relatedItem)
    {
        $ancestryId = 'ancestry-' . $relatedItem->getRelationshipTypeId();
        $this->relationshipGroups[$ancestryId] = array();
    }

    protected function addDescendant($name, RelatedItem $relatedItem)
    {
        $this->addAncestryGroup('3-descendants', $name, $relatedItem);
    }

    protected function addKidToRelatedItemsTreeNode($item, $label, RelatedItemsTreeNode $treeNode)
    {
        $itemTitle = ItemMetadata::getItemTitle($item);
        $relatedItem = new RelatedItem($item->id);
        $relatedItem->setItem($item);
        $relatedItem->setLabels($label);
        $this->kidId++;
        $kid = new RelatedItemsTreeNode($this->kidId, $itemTitle, $relatedItem);
        $treeNode->addKid($kid);
    }

    protected function addRelationshipGroup($name, RelatedItem $relatedItem)
    {
        $this->relationshipGroups[$name][$relatedItem->getItemId()] = $relatedItem;
    }

    protected function addSibling($name, RelatedItem $relatedItem)
    {
        $this->addAncestryGroup('1-siblings', $name, $relatedItem);
    }

    protected function combineGroupsWithSameItem($relatedItemGroups)
    {
        // Find related items that appear in more than one group where each of those groups contains only
        // that one related item. Those related items can be merged into a single hybrid group.

        // Create a list of groups that have just one item.
        $groupsWithOnlyOneRelatedItem = array();
        foreach ($relatedItemGroups as $groupName => $group)
        {
            if (count($group) == 1)
            {
                $keys = array_keys($group);
                $relatedItem = $group[$keys[0]];
                $itemId = $relatedItem->getItemId();
                $groupsWithOnlyOneRelatedItem[$itemId][] = $groupName;
            }
        }

        // Form the hybrid group name to be used for related items that will be combined.
        $relatedItemsToCombine = array();
        foreach ($groupsWithOnlyOneRelatedItem as $relatedItemId => $groups)
        {
            if (count($groups) > 1)
            {
                $hybridGroupName = $this->createHybridGroupName($groups);
                $relatedItemsToCombine[$relatedItemId] = $hybridGroupName;
            }
        }

        // Create a new list of groups that combines the same related item into its hybrid group.
        $combinedGroups = array();
        foreach ($relatedItemGroups as $oldGroupName => $group)
        {
            $newGroupName = $oldGroupName;
            if (count($group) == 1)
            {
                $keys = array_keys($group);
                $relatedItem = $group[$keys[0]];
                $itemId = $relatedItem->getItemId();

                if (array_key_exists($itemId, $relatedItemsToCombine))
                {
                    // This related item in is more than one group.
                    $newGroupName = $relatedItemsToCombine[$itemId];
                    if (!array_key_exists($newGroupName, $combinedGroups))
                    {
                        // Change the related item's relationship labels to reflect the hybrid relationship.
                        $relatedItem->changeRelationshipLabels($newGroupName);
                    }
                    else
                    {
                        // The first related item in the set to be combined has already been processed. Ignore this one.
                        continue;
                    }
                }
            }
            $combinedGroups[$newGroupName] = $group;
        }

        return $combinedGroups;
    }

    public static function containsItem($itemId, RelatedItemsTreeNode $node)
    {
        $kids = $node->getKids();
        foreach ($kids as $kid)
        {
            /* @var $kid RelatedItemsTreeNode */
            /* @var $relatedItem RelatedItem */
            $relatedItem = $kid->getRelatedItem();
            if (!empty($relatedItem && $relatedItem->getItemId() == $itemId))
                return true;
            if (self::containsItem($itemId, $kid))
                return true;
        }
        return false;
    }

    protected function createAncestry(RelatedItem $relatedItem)
    {
        $this->getSiblings($this->primaryItem->id, $relatedItem);
        $this->getAncestors($this->primaryItem->id, $relatedItem, 1);
        $this->getDescendants($this->primaryItem->id, $relatedItem, 1);
    }

    protected function createHybridGroupName($groups)
    {
        $hybridGroupName = '';
        foreach ($groups as $groupName)
        {
            if (!empty($hybridGroupName))
                $hybridGroupName .= ', ';
            $hybridGroupName .= $groupName;
        }

        return $hybridGroupName;
    }

    protected function createImplicitRelationshipsFor($item, RelatedItemsTree $tree, $elementName, $groupName)
    {
        $elementId = ItemMetadata::getElementIdForElementName($elementName);
        $title = ItemMetadata::getItemTitle($item, false);
        $label = $groupName;

        $results = ItemMetadata::getItemsWithElementValue($elementId, $title);

        if (empty($results))
            return null;

        $treeNode = new RelatedItemsTreeNode(0, $label);

        if (plugin_is_active('AvantSearch'))
        {
            // Form a URL for a search that will find all the related items. The URL is
            // emitted in the "See all n items" link that appears following a short list of items.
            $url = ItemSearch::getAdvancedSearchUrl($elementId, $title);
            $imageViewId = SearchResultsViewFactory::IMAGE_VIEW_ID;
            $url .= "&view=$imageViewId";
            $treeNode->setData($url);
        }

        foreach ($results as $result)
        {
            $itemId = $result['id'];
            if (RelatedItemsTree::containsItem($itemId, $tree->getRootNode()))
            {
                // This item is part of another relationship so don't emit it again.
                // If it's the only custom item, then don't emit the custom tree.
                if (count($results) == 1)
                    return null;
                else
                    continue;
            }
            $item = ItemMetadata::getItemFromId($itemId);
            if (empty($item))
            {
                // The user does not have access to the target item e.g. because it's private.
                continue;
            }
            $this->addKidToRelatedItemsTreeNode($item, $label, $treeNode);
        }

        return $treeNode;
    }

    protected function createKid($name, $relatedItem = null)
    {
        $this->kidId++;
        $kid = new RelatedItemsTreeNode($this->kidId, $name, $relatedItem);
        return $kid;
    }

    public function createCustomRelationshipsGroup($items, $groupName)
    {
        // This methods supports the AvantRelationships Custom Relationships configuration option. A custom callback
        // function that is specified using the option, can call this method passing a list of items that belong to
        // a custom relationship group.
        //
        // The custom callback function is called from AvantRelationships::createCustomRelationshipTreeNodes which
        // is in turn called from AvantRelationshipsPlugin::filterCustomRelationships which is in turn called from
        // RelatedItemsTree::insertImplicitRelationships for the filter 'custom_relationships'. This method returns
        // a single tree node containing one or more related items which insertImplicitRelationships inserts into
        // the tree as another node.

        $group = array();

        if (empty($items))
            return $group;

        $treeNode = new RelatedItemsTreeNode(0, $groupName);

        foreach ($items as $item)
        {
            $relatedItem = ItemMetadata::getItemFromId($item->id);
            if (empty($relatedItem))
                continue;

            $this->addKidToRelatedItemsTreeNode($item, $groupName, $treeNode);
        }

        $group[] = $treeNode;
        return $group;
    }

    protected function createRelatedItemGroups()
    {
        // Create arrays of relationship names with each element containing an array
        // of related items having that relationship name. The result is arrays of arrays.
        foreach ($this->relatedItems as $relatedItem)
        {
            if ($relatedItem->hasAncestry())
            {
                // Create a placeholder for this related item's ancestry groups so that they'll appear in the
                // proper order with respect to other non-ancestry groups. The order is determined by the
                // order column in the relationship_types table.
                $this->addAncestryGroupsPlaceholder($relatedItem);
                continue;
            }

            $relationshipName = $relatedItem->getRelationshipLabelPlural();
            $this->addRelationshipGroup($relationshipName, $relatedItem);
        }

        foreach ($this->relatedItems as $relatedItem)
        {
            if (!$relatedItem->hasAncestry())
            {
                continue;
            }
            $this->createAncestry($relatedItem);
        }

        // Merge ancestry groups with main groups so that the ancestry groups appear in the right sort order.
        $mergedGroups = $this->mergeGroups();

        // Combine any groups that all contain the same single item into a one group for that item.
        $combinedGroups = $this->combineGroupsWithSameItem($mergedGroups);

        return $combinedGroups;
    }

    protected function createSubtreeForDirectlyRelatedItems(RelatedItemsTreeNode $directKid, $relatedItems)
    {
        /* @var $relatedItem RelatedItem */
        foreach ($relatedItems as $relatedItem)
        {
            $kid = $this->createKid($relatedItem->getTitle(), $relatedItem);

            if ($relatedItem->hasDirectives())
            {
                // This directly related item has directives that indicate what kinds of indirect relationships
                // to look for. If any are found, create a subtree for each type of indirect relationship.
                $this->createSubtreesForIndirectlyRelatedItems($kid);
            }

            $directKid->addKid($kid);
        }
    }

    protected function createSubtreesForIndirectlyRelatedItems(RelatedItemsTreeNode $kid)
    {
        $relatedItem = $kid->getRelatedItem();
        $directives = $relatedItem->getDirectives();
        if (empty($directives))
            return;

        foreach ($directives as $indirectRelationshipTypeId)
        {
            $indirectlyRelatedItems = $this->getIndirectlyRelatedItems($relatedItem, $indirectRelationshipTypeId);
            if (count($indirectlyRelatedItems) == 0)
                continue;

            // Determine the name for this group of indirectly related items.
            $firstIndirectlyRelatedItem = $indirectlyRelatedItems[0];
            $relationshipLabel = count($indirectlyRelatedItems) == 1 ?
                $firstIndirectlyRelatedItem->getRelationshipLabelSingular() : $firstIndirectlyRelatedItem->getRelationshipLabelPlural();

            // Indirectly related items exist for this directive. Create a subtree for these items.
            $subtree = $this->createKid($relationshipLabel);
            foreach ($indirectlyRelatedItems as $indirectlyRelatedItem)
            {
                $subtree->addKid($this->createKid($indirectlyRelatedItem->getTitle(), $indirectlyRelatedItem));
                $this->hasIndirectlyRelatedItems = true;
            }
            $kid->addKid($subtree);
        }
    }

    protected function createTreeFromRelatedItemGroups()
    {
        $this->getRelatedItemsFromDatabase();
        $relatedItemGroups = $this->createRelatedItemGroups();

        foreach ($relatedItemGroups as $relationshipName => $relatedItems)
        {
            $kid = $this->createKid($relationshipName);
            $this->createSubtreeForDirectlyRelatedItems($kid, $relatedItems);
            $this->rootNode->addKid($kid);
        }
    }

    protected function getAncestorRelationships(RelatedItem $relatedItem)
    {
        $relationshipsTable = $this->db->getTable('Relationships');
        $excludedId = $relatedItem->getRelationshipId();
        return $relationshipsTable->findSourceRelationships($relatedItem, $relatedItem->getRelationshipTypeId(), $excludedId);
    }

    protected function getAncestors($childItemId, RelatedItem $relatedItem, $level)
    {
        if (!$relatedItem->isParentOf($childItemId))
            return;

        if ($level > self::MAX_ANCESTRY_LEVELS)
        {
            // Protect against the case where a child has inadvertently been set to be its own parent.
            return;
        }

        $ancestry = $relatedItem->getAncestry();
        $ancestry->setLevel($level);
        $ancestorsName = $ancestry->getAncestorsName($level);

        if ($level == 1)
        {
            $this->addAncestor($ancestorsName, $relatedItem);
            $this->getAncestors($childItemId, $relatedItem, $level + 1);
        }
        else
        {
            $ancestors = $this->getAncestorRelationships($relatedItem);
            foreach ($ancestors as $ancestor)
            {
                $ancestorRelatedItem = new RelatedItem($relatedItem->getItemId(), $ancestor);
                if ($ancestorRelatedItem->notAccessible())
                {
                    continue;
                }
                else
                {
                    $this->addAncestor($ancestorsName, $ancestorRelatedItem);
                    $this->getAncestors($relatedItem->getItemId(), $ancestorRelatedItem, $level + 1);
                }
            }
        }
    }

    protected function getDescendantRelationships(RelatedItem $relatedItem)
    {
        $relationshipsTable = $this->db->getTable('Relationships');
        $excludedId = $relatedItem->getRelationshipId();
        return $relationshipsTable->findTargetRelationships($relatedItem, $relatedItem->getRelationshipTypeId(), $excludedId);
    }

    protected function getDescendants($parentItemId, RelatedItem $relatedItem, $level)
    {
        if (!$relatedItem->isChildOf($parentItemId))
            return;

        $ancestry = $relatedItem->getAncestry();
        $ancestry->setLevel($level);
        $descendantsName = $ancestry->getDescendantsNames($level);

        if ($level == 1)
        {
            $this->addDescendant($descendantsName, $relatedItem);
            $this->getDescendants($parentItemId, $relatedItem, $level + 1);
        }
        else
        {

            if ($level > self::MAX_ANCESTRY_LEVELS)
            {
                // Protect against the case where a child has inadvertently been set to be its own parent.
                return;
            }

            $descendants = $this->getDescendantRelationships($relatedItem);
            foreach ($descendants as $descendant)
            {
                $descendantRelatedItem = new RelatedItem($relatedItem->getItemId(), $descendant);
                if ($descendantRelatedItem->notAccessible())
                {
                    continue;
                }
                else
                {
                    $this->addDescendant($descendantsName, $descendantRelatedItem);
                    $this->getDescendants($relatedItem->getItemId(), $descendantRelatedItem, $level + 1);
                }
            }
        }
    }

    protected function getIndirectlyRelatedItems(RelatedItem $relatedItem, $indirectRelationshipTypeId)
    {
        $relatedItems = array();
        $primaryRelationshipTypeId = $relatedItem->getRelationshipTypeId();

        if ($relatedItem->getRelationshipTypeId() != $primaryRelationshipTypeId)
        {
            // The related item does not have the requested primary relationship so ignore it. Note that this filtering
            // protects against including indirect relationships that would otherwise clutter the set of related items
            // shown for a primary item. To see the clutter, comment out the return below.
            return $relatedItems;
        }

        // Get the indirectly related items. Exclude the relatedItem so it won't come back as part of the set.
        // A negative $indirectRelationshipTypeId indicates that the directive is for indirect relationships where
        // the indirect item is the source of the indirect relationship. Positive means it's the target.
        $relationshipsTable = $this->db->getTable('Relationships');
        $excludedId = $relatedItem->getRelationshipId();
        if (intval($indirectRelationshipTypeId) < 0)
            $indirectRelationships = $relationshipsTable->findSourceRelationships($relatedItem, abs(intval($indirectRelationshipTypeId)), $excludedId);
        else
            $indirectRelationships = $relationshipsTable->findTargetRelationships($relatedItem, $indirectRelationshipTypeId, $excludedId);

        // Add each indirectly related item to the list of related items.
        foreach ($indirectRelationships as $indirectRelationship)
        {
            $indirectlyRelatedItem = new RelatedItem($relatedItem->getItemId(), $indirectRelationship);
            if ($indirectlyRelatedItem->notAccessible())
            {
                continue;
            }
            else
            {
                $relatedItems[] = $indirectlyRelatedItem;
            }
        }

        return $relatedItems;
    }

    public function getRelatedItems()
    {
        // Return related items that are directly related to the primary item. The list will include
        // children and parents (ancestry level = 2), but not siblings (ancestry level = 1) or
        // grandchildren or grandparents (ancestry level > 2).
        return $this->relatedItems;
    }

    protected function getRelatedItemsFromDatabase()
    {
        // Get every relationship where the primary item is the source or target of the relationship.
        // The relationships are returned sorted by the order column of the relationship_types table.
        $relationshipRecords = $this->db->getTable('Relationships')->findAllRelationships($this->primaryItem->id);

        foreach ($relationshipRecords as $relationshipRecord)
        {
            $relatedItem = new RelatedItem($this->primaryItem->id, $relationshipRecord);
            if ($relatedItem->notAccessible())
            {
                continue;
            }
            else
            {
                $this->relatedItems[] = $relatedItem;
            }
        }
    }

    public function getRootNode()
    {
        return $this->rootNode;
    }

    protected function getSiblings($primaryItemId, RelatedItem $relatedItem)
    {
        if (!$relatedItem->isParentOf($primaryItemId))
            return;

        // Find other related items that have the same parents as the related item.
        $siblings = $this->getDescendantRelationships($relatedItem);
        foreach ($siblings as $sibling)
        {
            $siblingsName = $relatedItem->getAncestry()->getSiblingsName();
            $siblingRelatedItem = new RelatedItem($this->primaryItem->id, $sibling);
            if ($siblingRelatedItem->notAccessible())
            {
                continue;
            }
            else
            {
                $this->addSibling($siblingsName, $siblingRelatedItem);
            }
        }
    }

    public function hasIndirectlyRelatedItems()
    {
        return $this->hasIndirectlyRelatedItems;
    }

    protected function insertImplicitRelationships()
    {
        $nodes = array();

        $implicitRelationshipsData = RelationshipsConfig::getOptionDataForImplicitRelationships();
        foreach ($implicitRelationshipsData as $elementId => $definition)
        {
            $elementName = $definition['name'];
            $label = $definition['label'];
            $node = $this->createImplicitRelationshipsFor($this->primaryItem, $this, $elementName, $label);
            if (!empty($node))
                $nodes[] = $node;
        }

        $customNodes = apply_filters('custom_relationships', $nodes, array('item' => $this->primaryItem, 'tree' => $this));
        $nodes = array_merge($nodes, $customNodes);

        foreach ($nodes as $node)
        {
            /* @var $node RelatedItemsTreeNode */
            $this->kidId++;
            $node->setId($this->kidId);
            $this->rootNode->addKid($node);
        }
    }

    protected function mergeGroups()
    {
        // This method is responsible for ensuring that:
        // 1. The set of groups for an ancestry appears in the right sort order among non-ancestry groups.
        // 2. That the ancestry types appear in this order: siblings, ancestors, descendants.
        //
        // To make this work, a placeholder group was inserted within the non-ancestry groups,
        // one placeholder for each type of ancestry. This logic replaces the placeholder with
        // that ancestry's groups.

        $allGroups = array();
        foreach ($this->relationshipGroups as $relationshipGroupName => $relationshipGroup)
        {
            $isPlaceholderGroup = false;
            foreach ($this->ancestries as $ancestryName => $ancestry)
            {
                if ($ancestryName == $relationshipGroupName)
                {
                    $isPlaceholderGroup = true;

                    // Sort the ancestry so that the types will appear as siblings, ancestors, descendants.
                    ksort($ancestry);

                    foreach ($ancestry as $ancestryType)
                    {
                        // The ancestry types are siblings, ancestors, descendants.
                        foreach ($ancestryType as $typeGroupName => $typeGroup)
                        {
                            // The type groups are e.g. for ancestors: parents, grandparents, great grandparents etc.
                            $allGroups[$typeGroupName] = $typeGroup;
                        }
                    }
                }
            }

            if (!$isPlaceholderGroup)
            {
                $allGroups[$relationshipGroupName] = $relationshipGroup;
            }
        }

        // Examine each group to determine if it's name should be plural or singular. The plural name is
        // used when a group is first created, so this logic only changes names that should be singular.
        $mergedGroups = array();
        foreach ($allGroups as $key => $group)
        {
            $groupName = $key;
            if (count($group) == 1)
            {
                $keys = array_keys($group);
                $relatedItem = $group[$keys[0]];
                $groupName = $relatedItem->getRelationshipLabelSingular();
            }
            $mergedGroups[$groupName] = $group;
        }

        return $mergedGroups;
    }
}
