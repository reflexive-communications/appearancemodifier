<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 * Generated from appearancemodifier/xml/schema/CRM/Appearancemodifier/AppearancemodifierProfile.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:6ff895e2d6b45c263172e9012793c4c7)
 */

use CRM_Appearancemodifier_ExtensionUtil as E;

/**
 * Database access object for the AppearancemodifierProfile entity.
 */
class CRM_Appearancemodifier_DAO_AppearancemodifierProfile extends CRM_Core_DAO
{
    const EXT = E::LONG_NAME;

    const TABLE_ADDED = '';

    /**
     * Static instance to hold the table name.
     *
     * @var string
     */
    public static $_tableName = 'civicrm_appearancemodifier_profile';

    /**
     * Should CiviCRM log any modifications to this table in the civicrm_log table.
     *
     * @var bool
     */
    public static $_log = false;

    /**
     * Unique Appearance-modifier Profile Setting ID
     *
     * @var int|string|null
     *   (SQL type: int unsigned)
     *   Note that values will be retrieved from the database as a string.
     */
    public $id;

    /**
     * FK to civicrm_uf_group
     *
     * @var int|string
     *   (SQL type: int unsigned)
     *   Note that values will be retrieved from the database as a string.
     */
    public $uf_group_id;

    /**
     * Layout handler class
     *
     * @var string
     *   (SQL type: varchar(511))
     *   Note that values will be retrieved from the database as a string.
     */
    public $layout_handler;

    /**
     * Color code of background in #ffffff format
     *
     * @var string
     *   (SQL type: varchar(15))
     *   Note that values will be retrieved from the database as a string.
     */
    public $background_color;

    /**
     * This text will be displayed after submit button
     *
     * @var string
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $additional_note;

    /**
     * Are consent checkboxes inverted?
     *
     * @var bool|string
     *   (SQL type: tinyint)
     *   Note that values will be retrieved from the database as a string.
     */
    public $invert_consent_fields;

    /**
     * Should we add placeholders?
     *
     * @var bool|string
     *   (SQL type: tinyint)
     *   Note that values will be retrieved from the database as a string.
     */
    public $add_placeholder;

    /**
     * Should we hide form labels?
     *
     * @var bool|string
     *   (SQL type: tinyint)
     *   Note that values will be retrieved from the database as a string.
     */
    public $hide_form_labels;

    /**
     * Color code of fonts in #ffffff format
     *
     * @var string
     *   (SQL type: varchar(15))
     *   Note that values will be retrieved from the database as a string.
     */
    public $font_color;

    /**
     * Select consent logic operation mode
     *
     * @var string
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $consent_field_behaviour;

    /**
     * Custom serialized data for PHP
     *
     * @var string
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $custom_settings;

    /**
     * Is Appearance-modifier enabled for this profile?
     *
     * @var bool|string
     *   (SQL type: tinyint)
     *   Note that values will be retrieved from the database as a string.
     */
    public $is_active;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->__table = 'civicrm_appearancemodifier_profile';
        parent::__construct();
    }

    /**
     * Returns localized title of this entity.
     *
     * @param bool $plural
     *   Whether to return the plural version of the title.
     */
    public static function getEntityTitle($plural = false)
    {
        return $plural ? E::ts('Appearance-modifier Profile Settings') : E::ts('Appearance-modifier Profile Setting');
    }

    /**
     * Returns foreign keys and entity references.
     *
     * @return array
     *   [CRM_Core_Reference_Interface]
     */
    public static function getReferenceColumns()
    {
        if (!isset(Civi::$statics[__CLASS__]['links'])) {
            Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
            Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'uf_group_id', 'civicrm_uf_group', 'id');
            CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
        }

        return Civi::$statics[__CLASS__]['links'];
    }

    /**
     * Returns all the column names of this table
     *
     * @return array
     */
    public static function &fields()
    {
        if (!isset(Civi::$statics[__CLASS__]['fields'])) {
            Civi::$statics[__CLASS__]['fields'] = [
                'id' => [
                    'name' => 'id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => E::ts('Appearance-modifier Profile Setting ID'),
                    'description' => E::ts('Unique Appearance-modifier Profile Setting ID'),
                    'required' => true,
                    'usage' => [
                        'import' => false,
                        'export' => false,
                        'duplicate_matching' => false,
                        'token' => false,
                    ],
                    'where' => 'civicrm_appearancemodifier_profile.id',
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'Number',
                    ],
                    'readonly' => true,
                    'add' => null,
                ],
                'uf_group_id' => [
                    'name' => 'uf_group_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => E::ts('UF Group ID'),
                    'description' => E::ts('FK to civicrm_uf_group'),
                    'required' => true,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.uf_group_id',
                    'export' => true,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'FKClassName' => 'CRM_Core_DAO_UFGroup',
                    'html' => [
                        'type' => 'Number',
                    ],
                    'add' => null,
                ],
                'layout_handler' => [
                    'name' => 'layout_handler',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => E::ts('Layout handler'),
                    'description' => E::ts('Layout handler class'),
                    'required' => false,
                    'maxlength' => 511,
                    'size' => CRM_Utils_Type::HUGE,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.layout_handler',
                    'export' => true,
                    'default' => null,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'Text',
                    ],
                    'add' => null,
                ],
                'background_color' => [
                    'name' => 'background_color',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => E::ts('Background color'),
                    'description' => E::ts('Color code of background in #ffffff format'),
                    'required' => false,
                    'maxlength' => 15,
                    'size' => CRM_Utils_Type::TWELVE,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.background_color',
                    'export' => true,
                    'default' => null,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'Text',
                    ],
                    'add' => null,
                ],
                'additional_note' => [
                    'name' => 'additional_note',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Additional note'),
                    'description' => E::ts('This text will be displayed after submit button'),
                    'required' => false,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.additional_note',
                    'export' => true,
                    'default' => null,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'Text',
                    ],
                    'add' => null,
                ],
                'invert_consent_fields' => [
                    'name' => 'invert_consent_fields',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => E::ts('Invert consent fields'),
                    'description' => E::ts('Are consent checkboxes inverted?'),
                    'required' => false,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.invert_consent_fields',
                    'export' => true,
                    'default' => null,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'CheckBox',
                    ],
                    'add' => null,
                ],
                'add_placeholder' => [
                    'name' => 'add_placeholder',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => E::ts('Add placeholder'),
                    'description' => E::ts('Should we add placeholders?'),
                    'required' => false,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.add_placeholder',
                    'export' => true,
                    'default' => null,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'CheckBox',
                    ],
                    'add' => null,
                ],
                'hide_form_labels' => [
                    'name' => 'hide_form_labels',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => E::ts('Hide form labels'),
                    'description' => E::ts('Should we hide form labels?'),
                    'required' => false,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.hide_form_labels',
                    'export' => true,
                    'default' => null,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'CheckBox',
                    ],
                    'add' => null,
                ],
                'font_color' => [
                    'name' => 'font_color',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => E::ts('Font color'),
                    'description' => E::ts('Color code of fonts in #ffffff format'),
                    'required' => false,
                    'maxlength' => 15,
                    'size' => CRM_Utils_Type::TWELVE,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.font_color',
                    'export' => true,
                    'default' => null,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'Text',
                    ],
                    'add' => '3.1',
                ],
                'consent_field_behaviour' => [
                    'name' => 'consent_field_behaviour',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Consent field operation mode'),
                    'description' => E::ts('Select consent logic operation mode'),
                    'required' => false,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.consent_field_behaviour',
                    'export' => true,
                    'default' => null,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'Text',
                    ],
                    'add' => '3.3',
                ],
                'custom_settings' => [
                    'name' => 'custom_settings',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Custom settings'),
                    'description' => E::ts('Custom serialized data for PHP'),
                    'required' => false,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.custom_settings',
                    'export' => true,
                    'default' => null,
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'serialize' => self::SERIALIZE_PHP,
                    'html' => [
                        'type' => 'Text',
                    ],
                    'add' => '3.3',
                ],
                'is_active' => [
                    'name' => 'is_active',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => E::ts('Is active?'),
                    'description' => E::ts('Is Appearance-modifier enabled for this profile?'),
                    'required' => true,
                    'usage' => [
                        'import' => true,
                        'export' => true,
                        'duplicate_matching' => true,
                        'token' => false,
                    ],
                    'import' => true,
                    'where' => 'civicrm_appearancemodifier_profile.is_active',
                    'export' => true,
                    'default' => '1',
                    'table_name' => 'civicrm_appearancemodifier_profile',
                    'entity' => 'AppearancemodifierProfile',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
                    'localizable' => 0,
                    'html' => [
                        'type' => 'CheckBox',
                    ],
                    'add' => null,
                ],
            ];
            CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
        }

        return Civi::$statics[__CLASS__]['fields'];
    }

    /**
     * Return a mapping from field-name to the corresponding key (as used in fields()).
     *
     * @return array
     *   Array(string $name => string $uniqueName).
     */
    public static function &fieldKeys()
    {
        if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
            Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
        }

        return Civi::$statics[__CLASS__]['fieldKeys'];
    }

    /**
     * Returns the names of this table
     *
     * @return string
     */
    public static function getTableName()
    {
        return self::$_tableName;
    }

    /**
     * Returns if this table needs to be logged
     *
     * @return bool
     */
    public function getLog()
    {
        return self::$_log;
    }

    /**
     * Returns the list of fields that can be imported
     *
     * @param bool $prefix
     *
     * @return array
     */
    public static function &import($prefix = false)
    {
        $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'appearancemodifier_profile', $prefix, []);

        return $r;
    }

    /**
     * Returns the list of fields that can be exported
     *
     * @param bool $prefix
     *
     * @return array
     */
    public static function &export($prefix = false)
    {
        $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'appearancemodifier_profile', $prefix, []);

        return $r;
    }

    /**
     * Returns the list of indices
     *
     * @param bool $localize
     *
     * @return array
     */
    public static function indices($localize = true)
    {
        $indices = [];

        return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
    }

}
