<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Api4\UFGroup;
use Civi\Api4\UFField;
use Civi\Api4\UFJoin;
use Civi\Api4\AppearancemodifierPetition;

/**
 * Testcases for Petition Form class.
 *
 * @group headless
 */
class CRM_Appearancemodifier_Form_PetitionConsentTest extends CRM_Appearancemodifier_Form_ConsentBase
{
    /*
     * It tests the preProcess function.
     */
    public function testPreProcess()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        $customFieldOther = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new CRM_Consentactivity_Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customFieldOther['id'],
            'consent-field-id' => 'do_not_email',
            'group-id' => '0',
        ];
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        UFJoin::create(false)
            ->addValue('module', 'CiviCampaign')
            ->addValue('entity_table', 'civicrm_survey')
            ->addValue('entity_id', $petition['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }
    /*
     * It tests the setDefaultValues function.
     */
    public function testSetDefaultValuesNoConfig()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new CRM_Consentactivity_Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        UFJoin::create(false)
            ->addValue('module', 'CiviCampaign')
            ->addValue('entity_table', 'civicrm_survey')
            ->addValue('entity_id', $petition['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }
    public function testSetDefaultValuesConfig()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new CRM_Consentactivity_Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        UFJoin::create(false)
            ->addValue('module', 'CiviCampaign')
            ->addValue('entity_table', 'civicrm_survey')
            ->addValue('entity_id', $petition['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        AppearancemodifierPetition::update(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->addValue('custom_settings', ['consentactivity' => ['custom_'.$customField['id'] => '1']])
            ->setLimit(1)
            ->execute()
            ->first();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form = new CRM_Appearancemodifier_Form_Petition();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }
    /*
     * It tests the buildQuickForm function.
     */
    public function testBuildQuickForm()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new CRM_Consentactivity_Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        \Civi\Api4\UFJoin::create(false)
            ->addValue('module', 'CiviCampaign')
            ->addValue('entity_table', 'civicrm_survey')
            ->addValue('entity_id', $petition['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form = new CRM_Appearancemodifier_Form_Petition();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->buildQuickForm(), 'buildQuickForm supposed to be empty.');
    }
    public function testPostProcess()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new CRM_Consentactivity_Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        \Civi\Api4\UFJoin::create(false)
            ->addValue('module', 'CiviCampaign')
            ->addValue('entity_table', 'civicrm_survey')
            ->addValue('entity_id', $petition['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form->setVar('_submitValues', [
            'original_color' => '0',
            'original_font_color' => '0',
            'transparent_background' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#000000',
            'additional_note' => 'My new additional note text',
            'petition_message' => 'My new petition message text',
            'consent_field_behaviour' => 'default',
            'target_number_of_signers' => '',
            'signers_block_position' => '',
            'custom_social_box' => '',
            'external_share_url' => 'my.link.com',
            'hide_form_labels' => '',
            'add_placeholder' => '',
            'preset_handler' => '',
            'consentactivity_custom_'.$customField['id'] => '1',
        ]);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedPetition = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->execute()
            ->first();
        self::assertSame('transparent', $modifiedPetition['background_color']);
        self::assertSame('My new additional note text', $modifiedPetition['additional_note']);
        self::assertSame('#000000', $modifiedPetition['font_color']);
        self::assertTrue(array_key_exists('custom_settings', $modifiedPetition));
        self::assertTrue(array_key_exists('consentactivity', $modifiedPetition['custom_settings']));
        self::assertSame('1', $modifiedPetition['custom_settings']['consentactivity']['custom_'.$customField['id']]);
    }
}