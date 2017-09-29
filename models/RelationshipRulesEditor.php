<?php

class RelationshipRulesEditor
{
    const ADD_RELATIONSHIP_RULE = 1;
    const REMOVE_RELATIONSHIP_RULE = 2;
    const UPDATE_RELATIONSHIP_RULE = 3;

    public static function addDefaultRule($description, $rule)
    {
        $relationshipRules = new RelationshipRules();
        $relationshipRules['description'] = $description;
        $relationshipRules['rule'] = $rule;
        $relationshipRules->save();
        return $relationshipRules['id'];
    }

    protected function addRule()
    {
        $relationshipRules = $this->getRelationshipRules();
        $success = $relationshipRules->save();
        $ruleId = $success ? $relationshipRules->id : 0;
        return json_encode(array('success' => $success, 'itemId' => $ruleId));
    }

    public function getRelationshipRules()
    {
        $rule = isset($_POST['rule']) ? $_POST['rule'] : '';
        $object = json_decode($rule, true);

        $relationshipRules = new RelationshipRules();
        $relationshipRules['id'] = isset($object['id']) ? intval($object['id']) : null;
        $relationshipRules['description'] = $object['description'];
        $relationshipRules['rule'] = $object['rule'];

        return $relationshipRules;
    }

    public static function getUsageCount($relationshipRuleId)
    {
        $db = get_db();
        $count = $db->getTable('RelationshipTypes')->getRelationshipTypeCountByRule($relationshipRuleId);
        return $count;
    }

    public function performAction($action)
    {
        switch ($action)
        {
            case RelationshipRulesEditor::ADD_RELATIONSHIP_RULE:
                return $this->addRule();

            case RelationshipRulesEditor::REMOVE_RELATIONSHIP_RULE:
                return $this->removeRule();

            case RelationshipRulesEditor::UPDATE_RELATIONSHIP_RULE:
                return $this->updateRule();

            default:
                return false;
        }
    }

    protected function removeRule()
    {
        $relationshipRuleId = isset($_POST['id']) ? $_POST['id'] : '';

        $db = get_db();
        $relationshipRules = $db->getTable('RelationshipRules')->find($relationshipRuleId);
        $success = false;
        if (self::getUsageCount($relationshipRuleId) == 0 && $relationshipRules)
        {
            $relationshipRules->delete();
            $success = true;
        }

        return json_encode(array('success' => $success));
    }

    protected function updateRule()
    {
        $relationshipRules = $this->getRelationshipRules();
        $success = $relationshipRules->save();
        return json_encode(array('success' => $success));
    }
}
