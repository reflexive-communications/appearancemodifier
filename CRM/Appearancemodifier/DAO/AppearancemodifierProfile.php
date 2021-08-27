<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from appearancemodifier/xml/schema/CRM/Appearancemodifier/AppearancemodifierProfile.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:bba5765a133035bc346df579945c5d6d)
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
    public static $_log = true;

    /**
     * Unique AppearancemodifierProfile ID
     *
     * @var int
     */
    public $id;

    /**
     * FK to UFGroup
     *
     * @var int
     */
    public $uf_group_id;

    /**
     * The alterContent handler function.
     *
     * @var text
     */
    public $layout_handler;

    /**
     * The color code of the background in #ffffff format.
     *
     * @var text
     */
    public $background_color;

    /**
     * The text that will be displayed after the submit button on the edit form.
     *
     * @var text
     */
    public $additional_note;

    /**
     * This field triggers the invert behaviour of the consent checkboxes.
     *
     * @var bool
     */
    public $invert_consent_fields;

    /**
     * Set the text input label as placeholder text in the input.
     *
     * @var bool
     */
    public $add_placeholder;

    /**
     * Hide the form labels and use only the placeholders.
     *
     * @var bool
     */
    public $hide_form_labels;

    /**
     * The color code of the fonts in #ffffff format.
     *
     * @var text
     */
    public $font_color;

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
        return $plural ? E::ts('Appearancemodifier Profiles') : E::ts('Appearancemodifier Profile');
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
          'description' => E::ts('Unique AppearancemodifierProfile ID'),
          'required' => true,
          'where' => 'civicrm_appearancemodifier_profile.id',
          'table_name' => 'civicrm_appearancemodifier_profile',
          'entity' => 'AppearancemodifierProfile',
          'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
          'localizable' => 0,
          'readonly' => true,
          'add' => null,
        ],
        'uf_group_id' => [
          'name' => 'uf_group_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('FK to UFGroup'),
          'required' => true,
          'where' => 'civicrm_appearancemodifier_profile.uf_group_id',
          'table_name' => 'civicrm_appearancemodifier_profile',
          'entity' => 'AppearancemodifierProfile',
          'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_UFGroup',
          'add' => null,
        ],
        'layout_handler' => [
          'name' => 'layout_handler',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => E::ts('Layout Handler'),
          'description' => E::ts('The alterContent handler function.'),
          'where' => 'civicrm_appearancemodifier_profile.layout_handler',
          'table_name' => 'civicrm_appearancemodifier_profile',
          'entity' => 'AppearancemodifierProfile',
          'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
          'localizable' => 0,
          'add' => null,
        ],
        'background_color' => [
          'name' => 'background_color',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => E::ts('Background Color'),
          'description' => E::ts('The color code of the background in #ffffff format.'),
          'where' => 'civicrm_appearancemodifier_profile.background_color',
          'table_name' => 'civicrm_appearancemodifier_profile',
          'entity' => 'AppearancemodifierProfile',
          'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
          'localizable' => 0,
          'add' => null,
        ],
        'additional_note' => [
          'name' => 'additional_note',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => E::ts('Additional Note'),
          'description' => E::ts('The text that will be displayed after the submit button on the edit form.'),
          'where' => 'civicrm_appearancemodifier_profile.additional_note',
          'table_name' => 'civicrm_appearancemodifier_profile',
          'entity' => 'AppearancemodifierProfile',
          'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
          'localizable' => 0,
          'add' => null,
        ],
        'invert_consent_fields' => [
          'name' => 'invert_consent_fields',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Invert Consent Fields'),
          'description' => E::ts('This field triggers the invert behaviour of the consent checkboxes.'),
          'where' => 'civicrm_appearancemodifier_profile.invert_consent_fields',
          'table_name' => 'civicrm_appearancemodifier_profile',
          'entity' => 'AppearancemodifierProfile',
          'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
          'localizable' => 0,
          'add' => null,
        ],
        'add_placeholder' => [
          'name' => 'add_placeholder',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Add Placeholder'),
          'description' => E::ts('Set the text input label as placeholder text in the input.'),
          'where' => 'civicrm_appearancemodifier_profile.add_placeholder',
          'table_name' => 'civicrm_appearancemodifier_profile',
          'entity' => 'AppearancemodifierProfile',
          'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
          'localizable' => 0,
          'add' => null,
        ],
        'hide_form_labels' => [
          'name' => 'hide_form_labels',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('Hide Form Labels'),
          'description' => E::ts('Hide the form labels and use only the placeholders.'),
          'where' => 'civicrm_appearancemodifier_profile.hide_form_labels',
          'table_name' => 'civicrm_appearancemodifier_profile',
          'entity' => 'AppearancemodifierProfile',
          'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
          'localizable' => 0,
          'add' => null,
        ],
        'font_color' => [
          'name' => 'font_color',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => E::ts('Font Color'),
          'description' => E::ts('The color code of the fonts in #ffffff format.'),
          'where' => 'civicrm_appearancemodifier_profile.font_color',
          'table_name' => 'civicrm_appearancemodifier_profile',
          'entity' => 'AppearancemodifierProfile',
          'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
          'localizable' => 0,
          'add' => '3.1',
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
