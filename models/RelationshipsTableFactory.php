<?php

class RelationshipsTableFactory
{
    public static function CreateDefaultRelationshipTypesAndRules()
    {
        $ruleIdArticle = RelationshipRulesEditor::addDefaultRule('Article', 'Type:^Article');
        $ruleIdArticleDwelling = RelationshipRulesEditor::addDefaultRule('Article with subject Dwelling or Places', 'Type:^Article;Subject:^(Structures, Dwelling|Places)');
        $ruleIdArticlePeople = RelationshipRulesEditor::addDefaultRule('Article with subject People', 'Type:^Article;Subject:^People');
        $ruleIdArticleStructures = RelationshipRulesEditor::addDefaultRule('Article with subject Structures', 'Type:^Article;Subject:^Structures');
        $ruleIdImage = RelationshipRulesEditor::addDefaultRule('Image', 'Type:^Image');

        $order = 1;

        // Create a 'depicts' type and set the Directives to that same type.
        $typeDepicts = RelationshipTypesEditor::addDefaultType($order++, $ruleIdImage, 'depicts', 'Related Articles,Related Article', $ruleIdArticle, 'depicted by', 'Images,Image');
        $depictsTypeId = $typeDepicts['id'];
        $typeDepicts['directives'] = $depictsTypeId;
        $typeDepicts->save();

        RelationshipTypesEditor::addDefaultType($order++, $ruleIdArticlePeople, 'married to', 'Married to', $ruleIdArticlePeople, 'married to', 'Married to');

        $ancestry = 'Siblings,Sibling; Parents,Parent:Grandparents,Grandparent:Great *; Children,Child:Grandchildren,Grandchild:Great *';
        RelationshipTypesEditor::addDefaultType($order++, $ruleIdArticlePeople, 'child of', 'Parents,Parent', $ruleIdArticlePeople, 'parent of', 'Children,Child', '', $ancestry);

        RelationshipTypesEditor::addDefaultType($order++, $ruleIdArticleStructures, 'designed by', 'Designed by', $ruleIdArticlePeople, 'designed', 'Designed');

        RelationshipTypesEditor::addDefaultType($order++, $ruleIdArticlePeople, 'resided at', 'Resided at', $ruleIdArticleDwelling, 'occupied by', 'Residents,Resident');
    }

    public static function CreateRelationshipsTable()
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

    public static function CreateRelationshipImagesTable()
    {
        $db = get_db();

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}relationship_images` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `item_id` int(10) unsigned NOT NULL,
            `identifier` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $db->query($sql);
    }

    public static function CreateRelationshipRulesTable()
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

    public static function CreateRelationshipTypesTable()
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
            `source_label` varchar(50) NOT NULL,
            `target_label` varchar(50) NOT NULL,
            `directives` varchar(1024) NOT NULL,
            `ancestry` varchar(1024) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $db->query($sql);
    }

    public static function DropRelatonshipsTable()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}relationships`";
        $db->query($sql);
    }

    public static function DropRelatonshipImagesTable()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}relationship_images`";
        $db->query($sql);
    }

    public static function DropRelatonshipRulesTable()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}relationship_rules`";
        $db->query($sql);
    }

    public static function DropRelatonshipTypesTable()
    {
        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}relationship_types`";
        $db->query($sql);
    }
}