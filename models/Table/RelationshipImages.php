<?php

class Table_RelationshipImages extends Omeka_Db_Table
{
    public function getImageItemIdentifier($itemId)
    {
        $relationshipImages = $this->getRelationshipImagesByItemId($itemId);
        return empty($relationshipImages) ? null : $relationshipImages->identifier;
    }

    public function getRelationshipImagesByItemId($itemId)
    {
        $select = $this->getSelect();
        $select->where("relationship_images.item_id = $itemId");
        $relationshipImages = $this->fetchObject($select);
        return $relationshipImages;
    }

    public function getRelationshipImagesByItemIdentifier($itemIdentifier)
    {
        $select = $this->getSelect();
        $select->where("relationship_images.identifier = $itemIdentifier");
        $relationshipImages = $this->fetchObjects($select);
        return $relationshipImages;
    }
}