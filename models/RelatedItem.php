<?php

// The RelatedItem class encapsulates relationship information for a single item. Unlike the RelationshipRecord
// class which contains information about both sides of a relationship, a RelatedItem object only describes an item
// that is related to a primary item. See the RelationshipModel class for an explanation of primary and related items.

class RelatedItem
{
    protected $ancestry;
    protected $directives = array();
    protected $identifier;
    protected $item;
    protected $itemId;
    protected $relationshipLabelPlural;
    protected $relationshipLabelSingular;
    protected $relationshipRecord;
    protected $title;

    public function __construct($primaryItemId, RelationshipRecord $relationshipRecord = null)
    {
        if (empty($relationshipRecord))
        {
            // The caller is constructing a RelatedItem that is to be initialized later;
            return;
        }

        $this->relationshipRecord = $relationshipRecord;
        $ancestryData = $relationshipRecord->getAncestry();
        $this->ancestry = empty($ancestryData) ? null : new RelatedItemsAncestry($relationshipRecord->getAncestry());

        if ($primaryItemId == $relationshipRecord->getSourceItemId())
        {
            $this->itemId = $relationshipRecord->getTargetItemId();
            $this->relationshipLabelPlural = $relationshipRecord->getSourceLabelPlural();
            $this->relationshipLabelSingular = $relationshipRecord->getSourceLabelSingular();

            // Directives apply only to the source item in a relationship.
            $this->directives = explode(',', $relationshipRecord->getDirectives());
            $this->directives = array_map('trim', $this->directives);
        }
        else
        {
            $this->itemId = $relationshipRecord->getSourceItemId();
            $this->relationshipLabelPlural = $relationshipRecord->getTargetLabelPlural();
            $this->relationshipLabelSingular = $relationshipRecord->getTargetLabelSingular();
        }

        $this->item = get_record_by_id('Item', $this->itemId);

        // Check to see if an item was returned. If not, the user does not have rights to access the item.
        if (!empty($this->item))
        {
            $this->setItemTitle();
            $this->setItemIdentifier();
        }
    }

    public function changeRelationshipLabels($text)
    {
        $this->relationshipLabelSingular = $text;
        $this->relationshipLabelPlural = $text;
    }

    public function getAncestry()
    {
        return $this->ancestry;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function getItemId()
    {
        return $this->itemId;
    }

    public function getDirectives()
    {
        return $this->directives;
    }

    public function getRelationshipId()
    {
        return $this->relationshipRecord->getRelationshipId();
    }

    public function getRelationshipTypeId()
    {
        return $this->relationshipRecord->getRelationshipTypeId();
    }

    public function getRelationshipLabelPlural()
    {
        return $this->relationshipLabelPlural;
    }

    public function getRelationshipLabelSingular()
    {
        return $this->relationshipLabelSingular;
    }

    public function getRelationshipName()
    {
        $relationshipRecord = $this->getRelationshipRecord();
        if ($this->itemId == $relationshipRecord->getSourceItemId())
            return $relationshipRecord->getTargetName();
        else
            return $relationshipRecord->getSourceName();
    }

    public function getRelationshipRecord()
    {
        return $this->relationshipRecord;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function hasAncestry()
    {
        return !empty($this->ancestry);
    }

    public function hasDirectives()
    {
        return !empty($this->directives);
    }

    public function notAccessible()
    {
        // Check that the item exists. It won't if it's private and no user is logged in.
        return empty($this->item);
    }

    public function isChildOf($itemId)
    {
        return $this->relationshipRecord->getTargetItemId() == $itemId;
    }

    public function isParentOf($itemId)
    {
        return $this->relationshipRecord->getSourceItemId() == $itemId;
    }

    public function setItem($item)
    {
        $this->item = $item;
        $this->itemId = $item->id;
        $this->setItemTitle();
        $this->setItemIdentifier();
    }

    protected function setItemIdentifier()
    {
        $this->identifier = metadata($this->item, array('Dublin Core', 'Identifier'), array('no_filter' => true));
    }

    protected function setItemTitle()
    {
        $this->title = metadata($this->item, array('Dublin Core', 'Title'), array('no_filter' => true));
    }

    public function setLabels($plural, $singular = null)
    {
        $this->relationshipLabelPlural = $plural;
        $this->relationshipLabelSingular = empty($singular) ? $plural : $singular;
    }

    public function usesCoverImage()
    {
        // Use the cover image unless this node is the parent of a subtree of indirectly related items.
        return !$this->hasDirectives();
    }
}