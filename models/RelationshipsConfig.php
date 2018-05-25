<?php

define('CONFIG_LABEL_IMPLICIT_RELATIONSHIPS', __('Implicit Relationships'));
define('CONFIG_LABEL_MAX_DIRECT_ITEMS', __('Max Direct Items'));
define('CONFIG_LABEL_MAX_INDIRECT_ITEMS', __('Max Indirect Items'));

class RelationshipsConfig extends ConfigOptions
{
    const OPTION_DELETE_TABLES = 'avantrelationships_delete_tables';
    const OPTION_IMPLICIT_RELATIONSHIPS = 'avantrelationships_implicit';
    const OPTION_MAX_DIRECT_ITEMS = 'avantrelationships_max_direct_shown';
    const OPTION_MAX_INDIRECT_ITEMS = 'avantrelationships_max_indirect_shown';
    const OPTION_VISUALIZATION = 'avantrelationships_visualizaton';

    public static function getOptionDataForImplicitRelationships()
    {
        return self::getOptionDefinitionData(self::OPTION_IMPLICIT_RELATIONSHIPS);
    }

    public static function getOptionTextForImplicitRelationships()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_IMPLICIT_RELATIONSHIPS];
        }
        else
        {
            $data = self::getOptionDataForImplicitRelationships();
            $text = '';

            foreach ($data as $elementId => $definition)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $name = $definition['name'];
                $label = $definition['label'];
                $text = "$name: $label";
            }
        }
        return $text;
    }

    public static function removeConfiguration()
    {
        delete_option(self::OPTION_DELETE_TABLES);
        delete_option(self::OPTION_IMPLICIT_RELATIONSHIPS);
        delete_option(self::OPTION_MAX_DIRECT_ITEMS);
        delete_option(self::OPTION_MAX_INDIRECT_ITEMS);
        delete_option(self::OPTION_VISUALIZATION);
    }

    public static function saveConfiguration()
    {
        self::saveOptionDataForMaxDirectItems();
        self::saveOptionDataForMaxIndirectItems();
        self::saveOptionDataForImplicitRelationships();

        set_option(self::OPTION_VISUALIZATION, $_POST[self::OPTION_VISUALIZATION]);
        set_option(self::OPTION_DELETE_TABLES, (int)(boolean)$_POST[self::OPTION_DELETE_TABLES]);
    }

    public static function saveOptionDataForImplicitRelationships()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_IMPLICIT_RELATIONSHIPS]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Text Field definitions are of the form: <element-name> ":" <label>
            $parts = array_map('trim', explode(':', $definition));

            $elementName = $parts[0];
            $label = isset($parts[1]) ? trim($parts[1]) : '';
            self::errorRowIf(strlen($label) == 0, CONFIG_LABEL_IMPLICIT_RELATIONSHIPS, $elementName, __("No label specified."));

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            self::errorIfNotElement($elementId, CONFIG_LABEL_IMPLICIT_RELATIONSHIPS, $elementName);

            $data[$elementId] = array('label' => $label);
        }

        set_option(self::OPTION_IMPLICIT_RELATIONSHIPS, json_encode($data));
    }

    public static function saveOptionDataForMaxDirectItems()
    {
        $maxDirectItems = intval($_POST[self::OPTION_MAX_DIRECT_ITEMS]);
        self::errorIf($maxDirectItems <= 0, CONFIG_LABEL_MAX_DIRECT_ITEMS, __('Value must be an integer greater than zero.'));
        set_option(self::OPTION_MAX_DIRECT_ITEMS, $maxDirectItems);
    }

    public static function saveOptionDataForMaxIndirectItems()
    {
        $maxIndirectItems = intval($_POST[self::OPTION_MAX_INDIRECT_ITEMS]);
        self::errorIf($maxIndirectItems <= 0, CONFIG_LABEL_MAX_INDIRECT_ITEMS, __('Value must be an integer greater than zero.'));
        set_option(self::OPTION_MAX_INDIRECT_ITEMS, $maxIndirectItems);
    }

    public static function setDefaultOptionValues()
    {
        set_option(self::OPTION_MAX_DIRECT_ITEMS, RelatedItemsListView::MAX_RELATED_ITEMS_SHOWN);
        set_option(self::OPTION_MAX_INDIRECT_ITEMS, RelatedItemsListView::MAX_INDIRECTLY_RELATED_ITEMS_SHOWN);
    }
}