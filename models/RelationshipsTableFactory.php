<?php

class RelationshipsTableFactory
{
    public static function createDefaultRelationshipTypesAndRules()
    {
        $ruleIdReference = RelationshipRulesEditor::addDefaultRule('Reference', 'Type:^Reference');
        $ruleIdReferencePeople = RelationshipRulesEditor::addDefaultRule('Reference with subject People', 'Type:^Reference;Subject:^People');
        $ruleIdImage = RelationshipRulesEditor::addDefaultRule('Image', 'Type:^Image');

        $order = 1;

        // Create a 'depicts' type and set the Directives to that same type.
        $typeDepicts = RelationshipTypesEditor::addDefaultType($order++, $ruleIdImage, 'depicts', 'Related References,Related Reference', $ruleIdReference, 'depicted by', 'Images,Image');
        $depictsTypeId = $typeDepicts['id'];
        $typeDepicts['directives'] = $depictsTypeId;
        $typeDepicts->save();

        RelationshipTypesEditor::addDefaultType($order++, $ruleIdReferencePeople, 'married to', 'Married to', $ruleIdReferencePeople, 'married to', 'Married to');

        $ancestry = 'Siblings,Sibling; Parents,Parent:Grandparents,Grandparent:Great *; Children,Child:Grandchildren,Grandchild:Great *';
        RelationshipTypesEditor::addDefaultType($order++, $ruleIdReferencePeople, 'child of', 'Parents,Parent', $ruleIdReferencePeople, 'parent of', 'Children,Child', '', $ancestry);

        RelationshipTypesEditor::addDefaultType($order++, 0, 'related to', 'Related to', 0, 'related to', 'Related to');
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