<?php

class RelationshipsTableFactory
{
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
            `determiner` varchar(50) NOT NULL,
            `description` varchar(512) DEFAULT NULL,
            `rule` varchar(512) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $db->query($sql);

        RelationshipRulesEditor::addDefaultRule();
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

        RelationshipTypesEditor::addDefaultType();
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