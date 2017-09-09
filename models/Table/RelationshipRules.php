<?php

class Table_RelationshipRules extends Omeka_Db_Table
{
    public function findPairsForSelectForm(array $options = array())
    {
        $pairs = array();
        $types = $this->findAll();

        foreach ($types as $type)
        {
            $description = $type->description;
            $pairs[$type->id] = $description;
        }

        // Sort by description.
        asort($pairs);


        // Prefix each rule with it's Id.
        foreach ($pairs as $typeId => $pair)
        {
            $pairs[$typeId] = "Rule $typeId: $pair";
        }

        return $pairs;
    }

    public function getRelationshipRule($relationshipRuleId)
    {
        $relationshipRule = $this->find($relationshipRuleId);

        $rule = new RelationshipRules;
        $rule['id'] = $relationshipRuleId;
        $rule['description'] = $relationshipRule->description;
        $rule['rule'] = $relationshipRule->rule;
        return $rule;
    }

    public function getRelationshipRules()
    {
        $select = $this->getSelect();
        $select->order('description');
        $rules = $this->fetchObjects($select);

        $list = array();

        foreach ($rules as $rule)
        {
            $list[] = $this->getRelationshipRule($rule['id']);
        }

        return $list;
    }
}