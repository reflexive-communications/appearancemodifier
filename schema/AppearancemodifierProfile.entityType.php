<?php

use CRM_Appearancemodifier_ExtensionUtil as E;

return [
    'name' => 'AppearancemodifierProfile',
    'table' => 'civicrm_appearancemodifier_profile',
    'class' => 'CRM_Appearancemodifier_DAO_AppearancemodifierProfile',
    'getInfo' => fn() => [
        'title' => E::ts('Appearance-modifier Profile Setting'),
        'title_plural' => E::ts('Appearance-modifier Profile Settings'),
        'description' => E::ts('This table contains settings for modified profiles'),
        'log' => false,
    ],
    'getFields' => fn() => [
        'id' => [
            'title' => E::ts('Appearance-modifier Profile Setting ID'),
            'sql_type' => 'int unsigned',
            'input_type' => 'Number',
            'required' => true,
            'description' => E::ts('Unique Appearance-modifier Profile Setting ID'),
            'primary_key' => true,
            'auto_increment' => true,
        ],
        'uf_group_id' => [
            'title' => E::ts('UF Group ID'),
            'sql_type' => 'int unsigned',
            'input_type' => 'Number',
            'required' => true,
            'description' => E::ts('FK to civicrm_uf_group'),
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
            'entity_reference' => [
                'entity' => 'UFGroup',
                'key' => 'id',
                'on_delete' => 'CASCADE',
            ],
        ],
        'layout_handler' => [
            'title' => E::ts('Layout handler'),
            'sql_type' => 'varchar(511)',
            'input_type' => 'Text',
            'description' => E::ts('Layout handler class'),
            'default' => null,
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
        'background_color' => [
            'title' => E::ts('Background color'),
            'sql_type' => 'varchar(15)',
            'input_type' => 'Text',
            'description' => E::ts('Color code of background in #ffffff format'),
            'default' => null,
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
        'additional_note' => [
            'title' => E::ts('Additional note'),
            'sql_type' => 'text',
            'input_type' => 'Text',
            'description' => E::ts('This text will be displayed after submit button'),
            'default' => null,
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
        'invert_consent_fields' => [
            'title' => E::ts('Invert consent fields'),
            'sql_type' => 'boolean',
            'input_type' => 'CheckBox',
            'description' => E::ts('Are consent checkboxes inverted?'),
            'default' => null,
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
        'add_placeholder' => [
            'title' => E::ts('Add placeholder'),
            'sql_type' => 'boolean',
            'input_type' => 'CheckBox',
            'description' => E::ts('Should we add placeholders?'),
            'default' => null,
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
        'hide_form_labels' => [
            'title' => E::ts('Hide form labels'),
            'sql_type' => 'boolean',
            'input_type' => 'CheckBox',
            'description' => E::ts('Should we hide form labels?'),
            'default' => null,
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
        'font_color' => [
            'title' => E::ts('Font color'),
            'sql_type' => 'varchar(15)',
            'input_type' => 'Text',
            'description' => E::ts('Color code of fonts in #ffffff format'),
            'add' => '3.1',
            'default' => null,
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
        'consent_field_behaviour' => [
            'title' => E::ts('Consent field operation mode'),
            'sql_type' => 'text',
            'input_type' => 'Text',
            'description' => E::ts('Select consent logic operation mode'),
            'add' => '3.3',
            'default' => null,
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
        'custom_settings' => [
            'title' => E::ts('Custom settings'),
            'sql_type' => 'text',
            'input_type' => 'Text',
            'description' => E::ts('Custom serialized data for PHP'),
            'add' => '3.3',
            'default' => null,
            'serialize' => constant('CRM_Core_DAO::SERIALIZE_PHP'),
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
        'is_active' => [
            'title' => E::ts('Is active?'),
            'sql_type' => 'boolean',
            'input_type' => 'CheckBox',
            'required' => true,
            'description' => E::ts('Is Appearance-modifier enabled for this profile?'),
            'default' => true,
            'usage' => [
                'import',
                'export',
                'duplicate_matching',
            ],
        ],
    ],
];
