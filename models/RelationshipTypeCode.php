<?php

class RelationshipTypeCode
{
    // A relationship's type and direction are sometimes combined into a code of the form
    // <direction>-<relationship_type_id> e.g. S-14 or T-21. The code is used when it's necessary
    // to know how a primary item relates to a related item, and how to use the relationship
    // in tables and logic. From the user's perspective, relationships are directional from a
    // primary item (i.e. the item being viewed or edited) to related item. However, in the
    // Relationships table, there is always a source item Id, relationship type Id, and a target item Id.
    // So for example, from the user's perspective, primary item X may be the parent-of related
    // item Y, but the the parent-of relationship is the inverse of the child-of relationship
    // and so Y is also the child-of X. Both the child-of and parent-of relationships have the
    // same relationship type Id e.g. 7 and so to distinguish between child-of and parent-of, that is,
    // the direction of the relationship, the relationship type Id gets prefixed to form a code
    // e.g S-7 or T-7 that holds both the Id and the direction. This class simply hides the
    // encoding/decoding of the code so that logic which uses the code doesn't know the code's format.

    const SOURCE_TO_TARGET = 'S';
    const TARGET_TO_SOURCE = 'T';

    public static function createRelationshipTypeCode($direction, $relationshipTypeId)
    {
        return "$direction-$relationshipTypeId";
    }

    public static function getDirection($relationshipTypeCode)
    {
        return substr($relationshipTypeCode, 0, 1);
    }

    public static function getRelationshipTypeId($relationshipTypeCode)
    {
        return substr($relationshipTypeCode, 2);
    }
}