<?php

class RelatedItemsEditor
{
    const ADD_RELATIONSHIP = 1;
    const REMOVE_RELATIONSHIP = 2;
    const UPDATE_RELATIONSHIP = 3;

    protected $advancedSearchRule;
    protected $db;
    protected $primaryItem;
    protected $relatedItemsModel;
    protected $validationErrorMessage;

    public function __construct($relatedItemsModel, $primaryItem)
    {
        $this->relatedItemsModel = $relatedItemsModel;
        $this->primaryItem = $primaryItem;
        $this->db = get_db();
    }

    protected function constructAdvancedQuery(array $elementRules)
    {
        $query = array();

        foreach ($elementRules as $elementRule)
        {
            $rule = array();
            $parts = explode(':', $elementRule);
            $elementName = $parts[0];
            $element = $this->db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', $elementName);

            if (empty($element) || count($parts) != 2)
            {
                return null;
            }

            $rule['joiner'] = 'and';
            $rule['element_id'] = $element->id;
            $rule['type'] = 'matches';
            $rule['terms'] = $parts[1];
            $query[] = $rule;
        }

        return $query;
    }

    protected function addRelationship($update = false)
    {
        $relatedItemIdentifier = isset($_POST['related']) ? $_POST['related'] : '';
        $relationshipTypeCode = isset($_POST['code']) ? $_POST['code'] : '';

        $relatedItem = ItemView::getItemFromIdentifier($relatedItemIdentifier);

        if (!$this->validateRelationshipParameters($this->primaryItem, $relationshipTypeCode, $relatedItemIdentifier, $relatedItem))
            return json_encode(array('success' => false, 'message' => $this->validationErrorMessage));

        if (!$this->validateRelationship($this->primaryItem, $relationshipTypeCode, $relatedItem))
            return json_encode(array('success' => false, 'message' => $this->validationErrorMessage));

        if (!$update && $this->relationshipExists($relationshipTypeCode, $relatedItem))
            return json_encode(array('success' => false, 'message' => $this->validationErrorMessage));

        $relationshipId = $this->insertItemRelationship($this->primaryItem, $relationshipTypeCode, $relatedItemIdentifier);
        $success = $relationshipId !== false;
        $message = $success ? 'Relationship Added' : $this->validationErrorMessage;
        $relatedItemLink = self::getRelatedItemLink($relatedItemIdentifier);
        return json_encode(array('success' => $success, 'message' => $message, 'link' => $relatedItemLink, 'relationshipId' => $relationshipId));
    }

    protected function addValidationError($message)
    {
        $this->validationErrorMessage = $message;
    }

    public function afterDeleteItem($args)
    {
        $item = $args['record'];

        // This code is only for Item objects, but it gets called when other kinds of records get deleted
        // such as an item's search_text table record. Ignore those other objects.
        if (!($item instanceof Item))
            return;

        $itemId = $item->id;

        // Find all relationships that have the deleted Item as their source or target.
        $relationshipsTable =  $this->db->getTable('Relationships');
        $sourceRelationships = $relationshipsTable->findBySourceItemId($itemId);
        $targetRelationships = $relationshipsTable->findByTargetItemId($itemId);
        $relationships = array_merge($sourceRelationships, $targetRelationships);

        // Delete all of the affected relationships.
        foreach ($relationships as $relationship)
        {
            $relationship->delete();
        }

        // Delete the cover image records for any other item that uses this item's image as its cover image.
        $itemIdentifier = ItemView::getItemIdentifier($item);
        $list = $this->db->getTable('RelationshipImages')->getRelationshipImagesByItemIdentifier($itemIdentifier);
        foreach ($list as $relationshipImages)
        {
            $relationshipImages->delete();
        }
    }

    public function afterSaveItem($args, $coverImageIdentifier)
    {
        $item = $args['record'];
        $this->updateCoverImageIdentifier($item, $coverImageIdentifier);
    }

    public function beforeSaveItem($args)
    {
        $item = $args['record'];

        if (empty($item->id))
        {
            // An empty Id means the item is being created for the first time.
            return;
        }

        $this->validateItemRelationships($item);

        $coverImageIdentifier = isset($_REQUEST['cover-image-identifier']) ? $_REQUEST['cover-image-identifier'] : '';
        $this->validateCoverImageIdentifier($item, $coverImageIdentifier);
    }

    protected function extendAdvancedSearchQueryForRelationships($params, $select)
    {
        // Extend the advanced search query to join in the relationships table to support filtering by relationship type.

        $option = isset($params['relationship-option']) ? $params['relationship-option'] : '';
        $relationshipTypeCode = isset($params['relationship-type-code']) ? $params['relationship-type-code'] : '';

        if ($option == 'has' || $option == 'not')
        {
            if (empty($relationshipTypeCode))
                return;

            $direction = RelationshipTypeCode::getDirection($relationshipTypeCode);
            $relationshipTypeId = RelationshipTypeCode::getRelationshipTypeId($relationshipTypeCode);

            $directionLetter = $direction == RelationshipTypeCode::SOURCE_TO_TARGET ? 'ST' : 'TS';
            $option .= "-$directionLetter";
        }

        $relationshipsTable = array('relationships' => "{$this->db->prefix}relationships");

        switch ($option)
        {
            case 'has-ST':
            {
                $select
                    ->join($relationshipsTable, "relationships.source_item_id = items.id", array())
                    ->where('relationships.relationship_type_id = ?', $relationshipTypeId);
                break;
            }

            case 'has-TS':
            {
                $select
                    ->join($relationshipsTable,"relationships.target_item_id = items.id", array())
                    ->where('relationships.relationship_type_id = ?', $relationshipTypeId);
                break;
            }

            case 'not-ST':
            {
                $select
                    ->joinLeft($relationshipsTable, "relationships.source_item_id = items.id", array())
                    ->where('relationships.relationship_type_id != ? OR relationships.relationship_type_id IS NULL', $relationshipTypeId);
                break;
            }

            case 'not-TS':
            {
                $select
                    ->joinLeft($relationshipsTable, "relationships.target_item_id = items.id", array())
                    ->where('relationships.relationship_type_id != ?  OR relationships.relationship_type_id IS NULL', $relationshipTypeId);
                break;
            }

            case 'any':
            {
                $select
                    ->join($relationshipsTable, "relationships.source_item_id = items.id OR
                     relationships.target_item_id = items.id", array());
                break;
            }

            case 'none':
            {
                $select
                    ->joinLeft($relationshipsTable, "relationships.source_item_id = items.id OR
                     relationships.target_item_id = items.id", array())
                    ->where('relationships.source_item_id IS NULL AND relationships.target_item_id IS NULL'
                    );
                break;
            }
        }
        $sql = (string)$select;
    }

    protected function formatRuleDescription($description)
    {
        // In English grammar, a determiner is a word that precedes a noun to express its reference in the context.
        // For relationship rules, the determiners are the indefinite articles 'a' and 'an'. This method chooses
        // the correct determiner based on the first letter of the rule description. This logic should be controlled
        // by a configuration setting, but for now it's based on whether the user's browser language is English.
        $determiner = '';
        $isEnglish = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) == 'en';
        if ($isEnglish && strlen($description) >= 1)
        {
            $firstLetterIsVowel = in_array(strtolower($description[0]), array('a', 'e', 'i', 'o', 'u'));
            $determiner = $firstLetterIsVowel ? 'an ' : 'a ';
        }

        return $determiner . $description;
    }

    public static function getRelatedItemLink($identifier)
    {
        $item = ItemView::getItemFromIdentifier($identifier);
        $href = url('items/show/' . $item->id);
        $title = ItemView::getItemTitle($item);
        return "<a href='$href' target='_blank'>$title</a>";
    }

    public function getRelationshipNamesSelectList()
    {
        return get_table_options('RelationshipTypes');
    }

    public function getSearchFilters($args)
    {
        // Create relationships part of the search filters message that appears at the top of the advanced search
        // results page to tell the user what search criteria they specified.
        $requestArray = $args['request_array'];
        if (!isset($requestArray['relationship-option']))
            return '';

        $code = $requestArray['relationship-type-code'];
        $relationshipNames = $this->getRelationshipNamesSelectList();
        $name = $relationshipNames[$code];
        $option = $requestArray['relationship-option'];

        if ($option == 'has')
            return __('Has relationship \'%s\'', $name);
        elseif ($option == 'not')
            return __('Does not have relationship \'%s\'', $name);
        elseif ($option == 'any')
            return __('Has any relationship');
        elseif ($option = 'none')
            return __('Has no relationships');
        else
            return '';
    }

    public function getValidationErrorMessage()
    {
        return $this->validationErrorMessage;
    }

    public function insertItemRelationship($primaryItem, $relationshipTypeCode, $relatedItemIdentifier)
    {
        $relatedItem = ItemView::getItemFromIdentifier($relatedItemIdentifier);

        $direction = RelationshipTypeCode::getDirection($relationshipTypeCode);
        $relationshipTypeId = RelationshipTypeCode::getRelationshipTypeId($relationshipTypeCode);

        $sourceId = $direction == RelationshipTypeCode::SOURCE_TO_TARGET ? $primaryItem->id : $relatedItem->id;
        $targetId = $direction == RelationshipTypeCode::SOURCE_TO_TARGET ? $relatedItem->id : $primaryItem->id;

        $relationship = new Relationships;
        $relationship->source_item_id = $sourceId;
        $relationship->relationship_type_id = $relationshipTypeId;
        $relationship->target_item_id = $targetId;

        $saved = $relationship->save();

        return $saved ? $relationship->id : false;
    }

    public function performAction($action)
    {
        switch ($action)
        {
            case RelatedItemsEditor::ADD_RELATIONSHIP:
                return $this->addRelationship();

            case RelatedItemsEditor::REMOVE_RELATIONSHIP:
                return $this->removeRelationship();

            case RelatedItemsEditor::UPDATE_RELATIONSHIP:
                return $this->updateRelationship();

            default:
                return false;
        }
    }

    public function processAdvancedSearchSql($args)
    {
        $params = $args['params'];

        // Save the advanced search options as rule text that will appear at the end of admin search results.
        // An admin can copy/paste this text for use as a relationship validation rule.
        if (isset($params['advanced']))
        {
            $advanced = $params['advanced'];
            $this->advancedSearchRule = json_encode($advanced);
        }

        $this->extendAdvancedSearchQueryForRelationships($params, $args['select']);
    }

    protected function relationshipExists($relationshipTypeCode, $relatedItem)
    {
        $relationshipTypeId = RelationshipTypeCode::getRelationshipTypeId($relationshipTypeCode);
        $direction = RelationshipTypeCode::getDirection($relationshipTypeCode);
        $sourceItemId = $direction == RelationshipTypeCode::SOURCE_TO_TARGET ? $this->primaryItem->id : $relatedItem->id;
        $targetItemId = $direction == RelationshipTypeCode::SOURCE_TO_TARGET ? $relatedItem->id : $this->primaryItem->id;

        if ($this->db->getTable('Relationships')->getRelationshipExists($relationshipTypeId, $sourceItemId, $targetItemId))
        {
            $this->addValidationError(__('This relationship already exists and cannot be added again.'));
            return true;
        }

        return false;
    }

    protected function removeRelationship()
    {
        $relationshipId = isset($_POST['id']) ? $_POST['id'] : '';
        $relationship = $this->db->getTable('Relationships')->find($relationshipId);
        $success = false;
        if ($relationship)
        {
            $relationship->delete();
            $success = true;
        }

        return json_encode(array('success' => $success));
    }

    protected function updateRelationship()
    {
        $data = $this->addRelationship(true);

        $response = json_decode($data, true);
        if ($response['success'] != true)
            return $data;

        $this->removeRelationship();

        return $data;
    }

    public function updateCoverImageIdentifier($item, $coverImageIdentifier)
    {
        $relationshipImages = $this->db->getTable('RelationshipImages')->getRelationshipImagesByItemId($item->id);

        if (empty($relationshipImages))
        {
            // No cover image is set for this item.
            if (empty($coverImageIdentifier))
            {
                return;
            }
            else
            {
                // Create a new cover image record.
                $relationshipImages = new RelationshipImages();
                $relationshipImages->item_id = $item->id;
            }
        }
        else
        {
            // A cover image is currently set fo this item.
            if (empty($coverImageIdentifier))
            {
                // The user has removed the cover image for this item.
                $relationshipImages->delete();
                return;
            }
        }

        if ($relationshipImages->identifier != $coverImageIdentifier)
        {
            // Either update or save this item's cover image.
            $relationshipImages->identifier = $coverImageIdentifier;
            $relationshipImages->save();
        }
    }

    public function validateCoverImageIdentifier($item, $coverImageIdentifier)
    {
        if (empty($coverImageIdentifier))
            return;

        $coverImageItem = ItemView::getItemFromIdentifier($coverImageIdentifier);
        if (empty($coverImageItem))
        {
            $item->addError(__('Cover Image'), __('%s is not a valid item Identifier', $coverImageIdentifier));
        }
        else if ($coverImageItem->id == $item->id)
        {
            $item->addError(__('Cover Image'), __('The cover image identifier cannot be this item\'s identifier'));
        }
    }

    public function validateItemRelationships($item)
    {
        /* @var $relationship RelationshipRecord */

        // This method is called when an item is saved. It validate all relationships that the item is the source of.
        // The validation is performed to help catch validation violations that can creep in after a relationship has
        // been successfully established due to a change made to the source or target item's Type or Subject. This code
        // only validates the source-to-target relationships so that if this item is the culprit in the violation, it
        // can be corrected and saved. If both the source and target relationships were validated, the error would get
        // detected in both directions making it impossible to correct because the target-to-source error would prevent
        // this item from being saved. The problem could be avoided if the validation logic checked the POSTED values
        // instead of the database values for this item.

        $relationships = $this->db->getTable('Relationships')->findAllRelationships($item->id);

        foreach ($relationships as $index => $relationship)
        {
            if ($relationship->getSourceItemId() != $item->id)
            {
                // Ignore the relationship because this item is the target.
                continue;
            }

            $relationshipTypeCode = RelationshipTypeCode::createRelationshipTypeCode(
                RelationshipTypeCode::SOURCE_TO_TARGET,
                $relationship->getRelationshipTypeId());
            $sourceItem = ItemView::getItemFromId($relationship->getSourceItemId());
            $targetItem = ItemView::getItemFromId($relationship->getTargetItemId());

            $relatedItemsEditor = new RelatedItemsEditor(null, $sourceItem);
            $valid = $relatedItemsEditor->validateRelationship($sourceItem, $relationshipTypeCode, $targetItem);
            if (!$valid)
            {
                $item->addError(__('RELATIONSHIP'), $relatedItemsEditor->getValidationErrorMessage());
            }
        }
    }

    public function validateRelationship($primaryItem, $relationshipTypeCode, $relatedItem)
    {
        $rules = $this->db->getTable('RelationshipTypes')->getRules($relationshipTypeCode);
        $relationshipName = $this->db->getTable('RelationshipTypes')->getRelationshipName($relationshipTypeCode);

        if (empty($rules))
        {
            $this->addValidationError(__('No rules have been set for relationship %s', $relationshipName));
            return false;
        }

        $valid = $this->validateRule($primaryItem->id, $rules['source'], __('this item'), $relationshipName);

        if (!$valid)
            return false;

        $valid = $this->validateRule($relatedItem->id,  $rules['target'], __('the related item'), $relationshipName);

        return $valid;
    }

    public function validateRelationshipParameters($primaryItem, $relationshipTypeCode, $relatedItemIdentifier, $relatedItem)
    {
        // Make sure there's a primary item. There will be none if the item's Identifier is not set.
        if (empty($primaryItem))
        {
            $this->addValidationError(__('This item has no Identifier. Give this item an Identifier, Save Changes, and then edit again to add relationships.'));
            return false;
        }

        // Make sure the admin provided both the relationship type and the target item.
        if (empty($relationshipTypeCode))
        {
            $this->addValidationError(__('Please select a relationship from the dropdown list'));
            return false;
        }

        // Make sure the admin provided both the relationship type and the target item.
        if (empty($relatedItemIdentifier))
        {
            $this->addValidationError(__('Please type the identifier number for the related item'));
            return false;
        }

        if (empty($relatedItem))
        {
            $this->addValidationError(__('Related item with Identifier \'%s\' does not exist', $relatedItemIdentifier));
            return false;
        }

        // Don't allow a self-referential relationship.
        if ($primaryItem->id == $relatedItem->id) {
            self::addValidationError(__('This item cannot be related to itself'));
            return false;
        }

        return true;
    }

    protected function validateRule($itemId, $rule, $violatorKind, $relationshipName)
    {
        if (empty($rule))
            return true;

        $ruleDescription = $this->formatRuleDescription($rule['description']);

        $elementRules = explode(';', $rule['rule']);
        $query = $this->constructAdvancedQuery($elementRules);
        if (empty($query))
        {
            $this->addValidationError(__('The \'%1$s\' relationship was not accepted because the rule \'%2$s\' is invalid. Please report the exact text of this error to your Omeka system administrator.', $relationshipName, $ruleDescription));
            return false;
        }

        $params = array();
        $params['advanced'] = $query;
        $table = $this->db->getTable('Item');
        $select = $table->getSelectForCount($params);
        $select->where('items.id = ?', $itemId);
        $count = $this->db->fetchOne($select);
        if ($count == 0)
        {
            $this->addValidationError(__('The \'%1$s\' relationship was not accepted because %2$s is not %3$s.', $relationshipName, $violatorKind, $ruleDescription));
            return false;
        }

        return true;
    }
}