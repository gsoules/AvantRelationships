<?php

class Table_RelationshipTypes extends Omeka_Db_Table
{
    public function findPairsForSelectForm(array $options = array())
    {
        $pairs = array();
        $types = $this->findAll();

        foreach ($types as $type)
        {
            $sourceName = $type->source_name;
            $targetName = $type->target_name;

            $sourceDirection = RelationshipTypeCode::SOURCE_TO_TARGET;

            // The same source and target names mean the relationship is bi-directional. Treat as source-to-target.
            $targetDirection = $sourceName == $targetName ? $sourceDirection : RelationshipTypeCode::TARGET_TO_SOURCE;

            $pairs[RelationshipTypeCode::createRelationshipTypeCode($sourceDirection, $type->id)] = $sourceName;
            $pairs[RelationshipTypeCode::createRelationshipTypeCode($targetDirection, $type->id)] = $targetName;
        }

        asort($pairs);
        return $pairs;
    }

    public function getRelationshipName($relationshipTypeCode)
    {
        $direction = RelationshipTypeCode::getDirection($relationshipTypeCode);
        $relationshipTypeId = RelationshipTypeCode::getRelationshipTypeId($relationshipTypeCode);

        $relationshipType = $this->find($relationshipTypeId);

        if ($direction == RelationshipTypeCode::SOURCE_TO_TARGET)
            return $relationshipType->source_name;
        else
            return $relationshipType->target_name;
    }

    public function getRelationshipTypeCountByRule($relationshipRuleId)
    {
        $select = $this->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('COUNT(*) AS count');
        $select->where("relationship_types.source_rule_id = $relationshipRuleId OR relationship_types.target_rule_id = $relationshipRuleId");
        $relationshipTypes= $this->fetchObject($select);
        return $relationshipTypes->count;
    }

    public function getRelationshipTypesAndRules()
    {
        $list = array();
        $types = $this->findPairsForSelectForm();

        foreach ($types as $relationshipTypeCode => $type)
        {
            $rules = $this->_db->getTable('RelationshipTypes')->getRules($relationshipTypeCode);

            $sourceRule = $rules['source']['description'];
            $targetRule = $rules['target']['description'];

            $list[$relationshipTypeCode][0] = RelationshipTypeCode::getRelationshipTypeId($relationshipTypeCode);
            $list[$relationshipTypeCode][1] = $sourceRule;
            $list[$relationshipTypeCode][2] = $type;
            $list[$relationshipTypeCode][3] = $targetRule;
        }

        return $list;
    }

    public function getRules($relationshipTypeCode)
    {
        $relationshipTypeId = RelationshipTypeCode::getRelationshipTypeId($relationshipTypeCode);
        $relationshipType = $this->find($relationshipTypeId);

        if (empty($relationshipType))
            return null;

        $direction = RelationshipTypeCode::getDirection($relationshipTypeCode);

        $sourceRuleId = $direction == RelationshipTypeCode::SOURCE_TO_TARGET ? $relationshipType->source_rule_id : $relationshipType->target_rule_id;
        $targetRuleId = $direction == RelationshipTypeCode::SOURCE_TO_TARGET ? $relationshipType->target_rule_id : $relationshipType->source_rule_id;

        $rules = array();

        $rulesTable = $this->_db->getTable('RelationshipRules');
        $rules['source'] = $sourceRuleId == 0 ? null : $rulesTable->getRelationshipRule($sourceRuleId);
        $rules['target'] = $targetRuleId == 0 ? null : $rulesTable->getRelationshipRule($targetRuleId);

        return $rules;
    }

    public function getRelationshipType($relationshipTypeId)
    {
        $relationshipType = $this->find($relationshipTypeId);

        $type = new RelationshipTypes();
        $type['id'] = $relationshipTypeId;
        $type['order'] = $relationshipType->order;
        $type['source_name'] = $relationshipType->source_name;
        $type['target_name'] = $relationshipType->target_name;
        $type['source_rule_id'] = $relationshipType->source_rule_id;
        $type['target_rule_id'] = $relationshipType->target_rule_id;
        $type['source_label'] = $relationshipType->source_label;
        $type['target_label'] = $relationshipType->target_label;
        $type['directives'] = $relationshipType->directives;
        $type['ancestry'] = $relationshipType->ancestry;

        return $type;
    }

    public function getRelationshipTypes()
    {
        $select = $this->getSelect();
        $select->order('order');
        $types = $this->fetchObjects($select);

        $list = array();

        foreach ($types as $type)
        {
            $list[] = $this->getRelationshipType($type['id']);
        }

        return $list;
    }
}
