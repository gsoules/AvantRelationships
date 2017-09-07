<?php

// The RelationshipRecord class encapsulate the raw relationship information fetched from the database
// by the Table_Relationships class. It contains information about the complete relationship including
// the source item, target item, the relationship between the items, and the source and target rules.

class RelationshipRecord
{
    protected $ancestry;
    protected $directives;
    protected $relationshipId;
    protected $relationshipTypeId;
    protected $sourceName;
    protected $sourceItemId;
    protected $sourceLabelParts;
    protected $sourceRuleId;
    protected $targetItemId;
    protected $targetLabelParts;
    protected $targetName;
    protected $targetRuleId;

    public function __construct($relationship)
    {
        $this->ancestry = $relationship->ancestry;
        $this->directives = $relationship->directives;
        $this->relationshipId = $relationship->id;
        $this->relationshipTypeId = $relationship->relationship_type_id;
        $this->sourceName = $relationship->source_name;
        $this->targetName = $relationship->target_name;
        $this->sourceItemId = $relationship->source_item_id;
        $this->sourceLabelParts = $this->parseLabel($relationship->source_label);
        $this->sourceRuleId = $relationship->source_rule_id;
        $this->targetItemId = $relationship->target_item_id;
        $this->targetLabelParts = $this->parseLabel($relationship->target_label);
        $this->targetRuleId = $relationship->target_rule_id;
    }

    protected function parseLabel($rawLabel)
    {
        $parts = explode(',', $rawLabel);
        if (count($parts) == 1)
        {
            $parts[] = $parts[0];
        }
        $parts[0] = trim($parts[0]);
        $parts[1] = trim($parts[1]);
        return $parts;
    }

    public function getAncestry()
    {
        return $this->ancestry;
    }

    public function getDirectives()
    {
        return $this->directives;
    }

    public function getRelationshipId()
    {
        return $this->relationshipId;
    }

    public function getRelationshipTypeId()
    {
        return $this->relationshipTypeId;
    }

    public function getSourceName()
    {
        return $this->sourceName;
    }

    public function getSourceItemId()
    {
        return $this->sourceItemId;
    }

    public function getSourceLabelPlural()
    {
        return $this->sourceLabelParts[0];
    }

    public function getSourceLabelSingular()
    {
        return $this->sourceLabelParts[1];
    }

    public function getSourceRuleId()
    {
        return $this->sourceRuleId;
    }

    public function getTargetItemId()
    {
        return $this->targetItemId;
    }

    public function getTargetLabelPlural()
    {
        return $this->targetLabelParts[0];
    }

    public function getTargetLabelSingular()
    {
        return $this->targetLabelParts[1];
    }

    public function getTargetName()
    {
        return $this->targetName;
    }

    public function getTargetRuleId()
    {
        return $this->targetRuleId;
    }
}