<?php

class RelationshipsTableFactory
{
    public static function createDefaultRelationshipTypesAndRules()
    {
        // Add rules.
        $ruleIdReference = RelationshipRulesEditor::addDefaultRule('Reference', 'Type:^Reference');
        $ruleIdReferencePeople = RelationshipRulesEditor::addDefaultRule('Reference with subject People', 'Type:^Reference;Subject:^People');
        $ruleIdImage = RelationshipRulesEditor::addDefaultRule('Image', 'Type:^Image');
        $ruleIdSet = RelationshipRulesEditor::addDefaultRule('Set', 'Type:^Set');
        $ruleIdAnyItem = RelationshipRulesEditor::addDefaultRule('Any Item except Set', 'Type:^(Reference|Document|Image|Map|Publication|Object)');
        $ruleIdDocumentaryItemOrObject = RelationshipRulesEditor::addDefaultRule('Documentary item or object', 'Type:^(Reference|Document|Publication|Object)');
        $ruleIdPeopleOrAnimals = RelationshipRulesEditor::addDefaultRule('Reference with subject People or Animals', 'Type:^Reference;Subject:^(People|Nature, Animals)');
        $ruleIdStructureOrPlaces = RelationshipRulesEditor::addDefaultRule('Reference with subject Structures or Places', 'Type:^Reference;Subject:^(Structures|Places)');

        // Add relationship types.
        $order = 1;

        // depicts / depicted by
        $typeDepicts = RelationshipTypesEditor::addDefaultType($order++, $ruleIdImage, 'depicts', 'Related References,Related Reference', $ruleIdReference, 'depicted by', 'Images,Image');
        $depictsTypeId = $typeDepicts['id'];
        $typeDepicts['directives'] = $depictsTypeId;
        $typeDepicts->save();

        // set of / has set
        RelationshipTypesEditor::addDefaultType($order++, $ruleIdSet, 'set of', 'Set of', $ruleIdReference, 'has set', 'Item Sets,Item Set');

        // married to
        RelationshipTypesEditor::addDefaultType($order++, $ruleIdReferencePeople, 'married to', 'Married to', $ruleIdReferencePeople, 'married to', 'Married to');

        // child of / parent of
        $ancestry = 'Siblings,Sibling; Parents,Parent:Grandparents,Grandparent:Great *; Children,Child:Grandchildren,Grandchild:Great *';
        RelationshipTypesEditor::addDefaultType($order++, $ruleIdReferencePeople, 'child of', 'Parents,Parent', $ruleIdReferencePeople, 'parent of', 'Children,Child', '', $ancestry);

        // about / mentioned it
        RelationshipTypesEditor::addDefaultType($order++, $ruleIdDocumentaryItemOrObject, 'about', 'About', $ruleIdReference, 'mentioned in', 'Mentioned in');

        // in set / set contains
        RelationshipTypesEditor::addDefaultType($order++, $ruleIdAnyItem, 'in set', 'In Item Set', $ruleIdSet, 'set contains', 'Item Set Contains');

        // resided at / occupied by
        RelationshipTypesEditor::addDefaultType($order++, $ruleIdPeopleOrAnimals, 'resided at', 'Resided at', $ruleIdStructureOrPlaces, 'occupied by', 'Residents,Resident');
    }

    public static function createRelationshipsTable()
    {
        $db = get_db();

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}relationships` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `source_item_id` int(10) unsigned NOT NULL,
            `relationship_type_id` int(10) unsigned NOT NULL,
            `target_item_id` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $db->query($sql);
    }

    public static function createRelationshipImagesTable()
    {
        $db = get_db();

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}relationship_images` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `item_id` int(10) unsigned NOT NULL,
            `identifier` varchar(64) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $db->query($sql);
    }

    public static function createRelationshipRulesTable()
    {
        $db = get_db();

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}relationship_rules` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `description` varchar(512) DEFAULT NULL,
            `rule` varchar(512) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $db->query($sql);
    }

    public static function createRelationshipTypesTable()
    {
        $db = get_db();

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}relationship_types` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `order` int(10) unsigned NOT NULL,
            `source_name` varchar(50) NOT NULL,
            `target_name` varchar(50) NOT NULL,
            `source_rule_id` int(10) unsigned NOT NULL,
            `target_rule_id` int(10) unsigned NOT NULL,
            `source_label` varchar(128) NOT NULL,
            `target_label` varchar(128) NOT NULL,
            `directives` varchar(1024) NOT NULL,
            `ancestry` varchar(1024) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $db->query($sql);
    }

    public static function dropRelatonshipsTable()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}relationships`";
        $db->query($sql);
    }

    public static function dropRelatonshipImagesTable()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}relationship_images`";
        $db->query($sql);
    }

    public static function dropRelatonshipRulesTable()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}relationship_rules`";
        $db->query($sql);
    }

    public static function dropRelatonshipTypesTable()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}relationship_types`";
        $db->query($sql);
    }
}