<?php

class RelationshipTypesEditor
{
    const ADD_RELATIONSHIP_TYPE = 1;
    const REMOVE_RELATIONSHIP_TYPE = 2;
    const UPDATE_RELATIONSHIP_TYPE = 3;
    const UPDATE_RELATIONSHIP_TYPE_ORDER = 4;

    public static function addDefaultType($order, $sourceRuleId, $sourceName, $sourceLabel, $targetRuleId, $targetName, $targetLabel, $directives = '', $ancestry = '')
    {
        $relationshipTypes = new RelationshipTypes();
        $relationshipTypes['order'] = $order;
        $relationshipTypes['source_rule_id'] = $sourceRuleId;
        $relationshipTypes['source_name'] = $sourceName;
        $relationshipTypes['source_label'] = $sourceLabel;
        $relationshipTypes['target_rule_id'] = $targetRuleId;
        $relationshipTypes['target_name'] = $targetName;
        $relationshipTypes['target_label'] = $targetLabel;
        $relationshipTypes['directives'] = $directives;
        $relationshipTypes['ancestry'] = $ancestry;
        $relationshipTypes->save();
        return $relationshipTypes;
    }

    protected function addType()
    {
        $relationshipTypes = $this->getRelationshipTypes();
        $success = $relationshipTypes->save();
        $typeId = $success ? $relationshipTypes->id : 0;
        return json_encode(array('success' => $success, 'itemId' => $typeId));
    }

    public function getRelationshipTypes()
    {
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $object = json_decode($type, true);

        $relationshipTypes = new RelationshipTypes();
        $relationshipTypes['id'] = isset($object['id']) ? intval($object['id']) : null;
        $relationshipTypes['order'] = intval($object['order']);
        $relationshipTypes['source_name'] = $object['sourceName'];
        $relationshipTypes['target_name'] = $object['targetName'];
        $relationshipTypes['source_rule_id'] = intval($object['sourceRuleId']);
        $relationshipTypes['target_rule_id'] = intval($object['targetRuleId']);
        $relationshipTypes['source_label'] = $object['sourceLabel'];
        $relationshipTypes['target_label'] = $object['targetLabel'];
        $relationshipTypes['directives'] = $object['directives'];
        $relationshipTypes['ancestry'] = $object['ancestry'];

        return $relationshipTypes;
    }

    public static function getUsageCount($relationshipTypeId)
    {
        $db = get_db();
        $count = $db->getTable('Relationships')->getRelationshipCountByType($relationshipTypeId);
        return $count;
    }

    public function performAction($action)
    {
        switch ($action)
        {
            case RelationshipTypesEditor::ADD_RELATIONSHIP_TYPE:
                return $this->addType();

            case RelationshipTypesEditor::REMOVE_RELATIONSHIP_TYPE:
                return $this->removeType();

            case RelationshipTypesEditor::UPDATE_RELATIONSHIP_TYPE_ORDER:
                return $this->updateOrder();

            case RelationshipTypesEditor::UPDATE_RELATIONSHIP_TYPE:
                return $this->updateType();

            default:
                return false;
        }
    }

    protected function removeType()
    {
        $relationshipTypeId = isset($_POST['id']) ? $_POST['id'] : '';

        $db = get_db();
        $relationshipTypes = $db->getTable('RelationshipTypes')->find($relationshipTypeId);
        $success = false;
        if (self::getUsageCount($relationshipTypeId) == 0 && $relationshipTypes)
        {
            $relationshipTypes->delete();
            $success = true;
        }

        return json_encode(array('success' => $success));
    }

    protected function updateOrder()
    {
        $order = isset($_POST['order']) ? $_POST['order'] : '';
        $saved = true;

        foreach ($order as $index => $relationshipTypeId)
        {
            $db = get_db();
            $relationshipTypes = $db->getTable('RelationshipTypes')->getRelationshipType($relationshipTypeId);
            $relationshipTypes['order'] = $index + 1;
            $saved = $relationshipTypes->save();
            if (!$saved)
                break;
        }

        return json_encode(array('success' => $saved));
    }

    protected function updateType()
    {
        $relationshipTypes = $this->getRelationshipTypes();
        $success = $relationshipTypes->save();
        return json_encode(array('success' => $success));
    }
}
