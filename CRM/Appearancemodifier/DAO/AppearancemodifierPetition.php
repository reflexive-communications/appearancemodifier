<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 * Generated from appearancemodifier/xml/schema/CRM/Appearancemodifier/AppearancemodifierPetition.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:1e100c0ce73f0cabe6aaf453bd00da42)
 */

use CRM_Appearancemodifier_ExtensionUtil as E;

/**
 * Database access object for the AppearancemodifierPetition entity.
 */
class CRM_Appearancemodifier_DAO_AppearancemodifierPetition extends CRM_Core_DAO
{
    const EXT = E::LONG_NAME;

    const TABLE_ADDED = '';

    /**
     * Static instance to hold the table name.
     *
     * @var string
     */
    public static $_tableName = 'civicrm_appearancemodifier_petition';

    /**
     * Should CiviCRM log any modifications to this table in the civicrm_log table.
     *
     * @var bool
     */
    public static $_log = true;

    /**
     * Unique AppearancemodifierPetition ID
     *
     * @var int|string|null
     *   (SQL type: int unsigned)
     *   Note that values will be retrieved from the database as a string.
     */
    public $id;

    /**
     * FK to Survey
     *
     * @var int|string|null
     *   (SQL type: int unsigned)
     *   Note that values will be retrieved from the database as a string.
     */
    public $survey_id;

    /**
     * The alterContent handler function.
     *
     * @var string|null
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $layout_handler;

    /**
     * The color code of the background in #ffffff format.
     *
     * @var string|null
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $background_color;

    /**
     * The text that will be displayed after the submit button on the edit form.
     *
     * @var string|null
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $additional_note;

    /**
     * The text that will be displayed in the petition message input.
     *
     * @var string|null
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $petition_message;

    /**
     * This field triggers the invert behaviour of the consent checkboxes.
     *
     * @var bool|string|null
     *   (SQL type: tinyint)
     *   Note that values will be retrieved from the database as a string.
     */
    public $invert_consent_fields;

    /**
     * The target number of the petition signers.
     *
     * @var int|string|null
     *   (SQL type: int)
     *   Note that values will be retrieved from the database as a string.
     */
    public $target_number_of_signers;

    /**
     * This field triggers the custom social sharing layout.
     *
     * @var bool|string|null
     *   (SQL type: tinyint)
     *   Note that values will be retrieved from the database as a string.
     */
    public $custom_social_box;

    /**
     * This link will be shared in the custom social box.
     *
     * @var string|null
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $external_share_url;

    /**
     * Set the text input label as placeholder text in the input.
     *
     * @var bool|string|null
     *   (SQL type: tinyint)
     *   Note that values will be retrieved from the database as a string.
     */
    public $add_placeholder;

    /**
     * Hide the form labels and use only the placeholders.
     *
     * @var bool|string|null
     *   (SQL type: tinyint)
     *   Note that values will be retrieved from the database as a string.
     */
    public $hide_form_labels;

    /**
     * The color code of the fonts in #ffffff format.
     *
     * @var string|null
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $font_color;

    /**
     * The position where the number of petition signers is displayed.
     *
     * @var string|null
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $signers_block_position;

    /**
     * This field describes the behaviour of the consent logic.
     *
     * @var string|null
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $consent_field_behaviour;

    /**
     * Serialized data for PHP usage
     *
     * @var string|null
     *   (SQL type: text)
     *   Note that values will be retrieved from the database as a string.
     */
    public $custom_settings;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->__table = 'civicrm_appearancemodifier_petition';
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
        return $plural ? E::ts('Appearancemodifier Petitions') : E::ts('Appearancemodifier Petition');
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
            Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'survey_id', 'civicrm_survey', 'id');
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
                    'description' => E::ts('Unique AppearancemodifierPetition ID'),
                    'required' => true,
                    'where' => 'civicrm_appearancemodifier_petition.id',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'readonly' => true,
                    'add' => null,
                ],
                'survey_id' => [
                    'name' => 'survey_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'description' => E::ts('FK to Survey'),
                    'where' => 'civicrm_appearancemodifier_petition.survey_id',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'FKClassName' => 'CRM_Campaign_DAO_Survey',
                    'add' => null,
                ],
                'layout_handler' => [
                    'name' => 'layout_handler',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Layout Handler'),
                    'description' => E::ts('The alterContent handler function.'),
                    'where' => 'civicrm_appearancemodifier_petition.layout_handler',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'background_color' => [
                    'name' => 'background_color',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Background Color'),
                    'description' => E::ts('The color code of the background in #ffffff format.'),
                    'where' => 'civicrm_appearancemodifier_petition.background_color',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'additional_note' => [
                    'name' => 'additional_note',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Additional Note'),
                    'description' => E::ts('The text that will be displayed after the submit button on the edit form.'),
                    'where' => 'civicrm_appearancemodifier_petition.additional_note',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'petition_message' => [
                    'name' => 'petition_message',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Petition Message'),
                    'description' => E::ts('The text that will be displayed in the petition message input.'),
                    'where' => 'civicrm_appearancemodifier_petition.petition_message',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'invert_consent_fields' => [
                    'name' => 'invert_consent_fields',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => E::ts('Invert Consent Fields'),
                    'description' => E::ts('This field triggers the invert behaviour of the consent checkboxes.'),
                    'where' => 'civicrm_appearancemodifier_petition.invert_consent_fields',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'target_number_of_signers' => [
                    'name' => 'target_number_of_signers',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => E::ts('Target Number Of Signers'),
                    'description' => E::ts('The target number of the petition signers.'),
                    'where' => 'civicrm_appearancemodifier_petition.target_number_of_signers',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'custom_social_box' => [
                    'name' => 'custom_social_box',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => E::ts('Custom Social Box'),
                    'description' => E::ts('This field triggers the custom social sharing layout.'),
                    'where' => 'civicrm_appearancemodifier_petition.custom_social_box',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'external_share_url' => [
                    'name' => 'external_share_url',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('External Share Url'),
                    'description' => E::ts('This link will be shared in the custom social box.'),
                    'where' => 'civicrm_appearancemodifier_petition.external_share_url',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'add_placeholder' => [
                    'name' => 'add_placeholder',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => E::ts('Add Placeholder'),
                    'description' => E::ts('Set the text input label as placeholder text in the input.'),
                    'where' => 'civicrm_appearancemodifier_petition.add_placeholder',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'hide_form_labels' => [
                    'name' => 'hide_form_labels',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => E::ts('Hide Form Labels'),
                    'description' => E::ts('Hide the form labels and use only the placeholders.'),
                    'where' => 'civicrm_appearancemodifier_petition.hide_form_labels',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => null,
                ],
                'font_color' => [
                    'name' => 'font_color',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Font Color'),
                    'description' => E::ts('The color code of the fonts in #ffffff format.'),
                    'where' => 'civicrm_appearancemodifier_petition.font_color',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => '3.1',
                ],
                'signers_block_position' => [
                    'name' => 'signers_block_position',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Signers Block Position'),
                    'description' => E::ts('The position where the number of petition signers is displayed.'),
                    'where' => 'civicrm_appearancemodifier_petition.signers_block_position',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => '3.2',
                ],
                'consent_field_behaviour' => [
                    'name' => 'consent_field_behaviour',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Consent Field Behaviour'),
                    'description' => E::ts('This field describes the behaviour of the consent logic.'),
                    'where' => 'civicrm_appearancemodifier_petition.consent_field_behaviour',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'add' => '3.3',
                ],
                'custom_settings' => [
                    'name' => 'custom_settings',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => E::ts('Custom Settings'),
                    'description' => E::ts('Serialized data for PHP usage'),
                    'where' => 'civicrm_appearancemodifier_petition.custom_settings',
                    'table_name' => 'civicrm_appearancemodifier_petition',
                    'entity' => 'AppearancemodifierPetition',
                    'bao' => 'CRM_Appearancemodifier_DAO_AppearancemodifierPetition',
                    'localizable' => 0,
                    'serialize' => self::SERIALIZE_PHP,
                    'add' => '3.3',
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
        $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'appearancemodifier_petition', $prefix, []);

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
        $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'appearancemodifier_petition', $prefix, []);

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
