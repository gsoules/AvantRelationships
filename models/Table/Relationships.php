<?php

class Table_Relationships extends Omeka_Db_Table
{
    protected function createRelationshipRecords($select)
    {
        $relationships = $this->fetchObjects($select);

        $records = array();

        foreach ($relationships as $relationship)
        {
            $records[] = new RelationshipRecord($relationship);
        }

        return $records;
    }

    public function findAllRelationships($itemId)
    {
        $select = $this->getSelect();
        $select->where("relationships.source_item_id = $itemId OR relationships.target_item_id = $itemId");

        return $this->createRelationshipRecords($select);
    }

    public function findBySourceItemId($sourceItemId)
    {
        $select = $this->getSelect()->where('relationships.source_item_id = ?', $sourceItemId);
        return $this->fetchObjects($select);
    }

    public function findByTargetItemId($targetItemId)
    {
        $select = $this->getSelect()->where('relationships.target_item_id = ?', $targetItemId);
        return $this->fetchObjects($select);
    }

    public function findSourceRelationships(RelatedItem $relatedItem, $relationshipTypeId, $excludedId)
    {
        // Find relationships named $relationshipName that have $relatedItem's item as their source.
        $sourceItemId = $relatedItem->getItemId();

        $select = $this->getSelect();
        $select->where("relationships.source_item_id = $sourceItemId");

        $select->where("relationship_types.id = '$relationshipTypeId'");
        $select->where("relationships.id <> $excludedId");

        return $this->createRelationshipRecords($select);
    }

    public function findTargetRelationships(RelatedItem $relatedItem, $relationshipTypeId, $excludedId)
    {
        // Find relationships named $relationshipName that have $relatedItem's item as their target.
        $targetItemId = $relatedItem->getItemId();

        $select = $this->getSelect();
        $select->where("relationships.target_item_id = $targetItemId");

        $select->where("relationship_types.id = '$relationshipTypeId'");
        $select->where("relationships.id <> $excludedId");

        return $this->createRelationshipRecords($select);
    }

    public function getRelationshipCountByType($relationshipTypeId)
    {
        $select = $this->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('COUNT(*) AS count');
        $select->where("relationships.relationship_type_id = $relationshipTypeId");
        $relationships = $this->fetchObject($select);
        return $relationships->count;
    }

    public function getRelationshipExists($relationshipTypeId, $sourceItemId, $targetItemId)
    {
        $select = $this->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('COUNT(*) AS count');
        $select->where("relationships.relationship_type_id = $relationshipTypeId");
        $select->where("relationships.source_item_id = $sourceItemId");
        $select->where("relationships.target_item_id = $targetItemId");
        $relationships = $this->fetchObject($select);
        return $relationships->count > 0;
    }

    public function getRelationshipsList()
    {
        $select = parent::getSelect();
        $relationships = $this->fetchObjects($select);
        return $relationships;
    }

    public function getSelect()
    {
        $db = $this->getDb();

        // Get the base query that has the the Relationships table in its from clause.
        $select = parent::getSelect();

        // Join with the RelationshipTypes table.
        $select->join(
            array('relationship_types' => "{$db->prefix}relationship_types"),
            'relationships.relationship_type_id = relationship_types.id',
            array(
                'source_name',
                'target_name',
                'source_label',
                'target_label',
                'source_rule_id',
                'target_rule_id',
                'directives',
                'ancestry',
                'order')
        );

        // Join with the RelationshipRules table, once for the source rule and again for the target rule.
        $select->joinLeft(
            array('relationship_rules_source' => "{$db->prefix}relationship_rules"),
            'relationship_types.source_rule_id = relationship_rules_source.id',
            array('source_rule' => 'rule')
        );
        $select->joinLeft(
            array('relationship_rules_target' => "{$db->prefix}relationship_rules"),
            'relationship_types.target_rule_id = relationship_rules_target.id',
            array('target_rule' => 'rule')
        );

        // Order the results based on order of importance and then by position in the Relationships
        // table which corresponds to the order in which relationships got added to the table.
        $select->order('relationship_types.order');
        $select->order('relationships.id');

        $sql = (string)$select; // For debugging
        return $select;
    }
}