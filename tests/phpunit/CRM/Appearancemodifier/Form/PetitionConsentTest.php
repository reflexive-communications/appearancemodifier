<?php

use Civi\Api4\AppearancemodifierPetition;
use Civi\Api4\UFField;
use Civi\Api4\UFGroup;
use Civi\Api4\UFJoin;
use Civi\Appearancemodifier\HeadlessTestCase;
use Civi\Consentactivity\Config;

/**
 * @group headless
 */
class CRM_Appearancemodifier_Form_PetitionConsentTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
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
        // setup consent activity configuration
        $config = new Config('consentactivity');
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
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
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
        $form->preProcess();
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesConfig()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup consent activity configuration
        $config = new Config('consentactivity');
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
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
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
        $form->preProcess();
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcess()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup consent activity configuration
        $config = new Config('consentactivity');
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
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
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
        $form->setVar('_submitValues', [
            'is_active' => '1',
            'original_color' => '0',
            'original_font_color' => '0',
            'transparent_background' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#000000',
            'additional_note' => 'My new additional note text',
            'petition_message' => 'My new petition message text',
            'disable_petition_message_edit' => '0',
            'consent_field_behaviour' => 'default',
            'target_number_of_signers' => '',
            'signers_block_position' => '',
            'custom_social_box' => '',
            'external_share_url' => 'my.link.com',
            'hide_form_labels' => '',
            'add_placeholder' => '',
            'preset_handler' => '',
            'hide_form_title' => '',
            'send_size_when_embedded' => '',
            'send_size_to_when_embedded' => '*',
            'add_check_all_checkbox' => '',
            'check_all_checkbox_label' => '',
            'consentactivity_custom_'.$customField['id'] => '1',
        ]);

        $form->preProcess();
        $form->buildQuickForm();
        $form->postProcess();

        $modifiedPetition = AppearancemodifierPetition::get(false)
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
