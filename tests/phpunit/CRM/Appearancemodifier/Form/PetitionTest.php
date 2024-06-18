<?php

use Civi\Api4\AppearancemodifierPetition;
use Civi\Appearancemodifier\HeadlessTestCase;

class DummyPetitionPresetProviderClass
{
    /**
     * @return string[]
     */
    public static function getPresets(): array
    {
        return [
            'is_active' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#000000',
            'additional_note' => 'My default additional note text',
            'petition_message' => 'My new petition message text',
            'invert_consent_fields' => '',
            'target_number_of_signers' => '',
            'signers_block_position' => '',
            'custom_social_box' => '',
            'external_share_url' => 'my.link.com',
            'hide_form_labels' => '',
            'add_placeholder' => '',
        ];
    }
}

/**
 * @group headless
 */
class CRM_Appearancemodifier_Form_PetitionTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPreProcessMissingPetition()
    {
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $petition = $petition['values'][0];
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'] + 1;
        $_GET['pid'] = $petition['id'] + 1;
        $_POST['pid'] = $petition['id'] + 1;
        self::expectException(CRM_Core_Exception::class);
        $form->preProcess();
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesOriginalColor()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $petition = $petition['values'][0];
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
    public function testSetDefaultValuesTransparentColor()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $petition = $petition['values'][0];
        AppearancemodifierPetition::update(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form->preProcess();
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['transparent_background']);
        self::assertNull($defaults['background_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesConsentFieldBehaviour()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $petition = $petition['values'][0];
        AppearancemodifierPetition::update(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form->preProcess();
        $defaults = $form->setDefaultValues();
        self::assertSame('default', $defaults['consent_field_behaviour']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesDisabledMessageEdition()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $petition = $petition['values'][0];
        AppearancemodifierPetition::update(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->addValue('custom_settings', ['disable_petition_message_edit' => '1'])
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form->preProcess();
        $defaults = $form->setDefaultValues();
        self::assertSame('1', $defaults['disable_petition_message_edit']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessWithoutPresets()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $petition = $petition['values'][0];
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form->setVar('_submitValues', [
            'is_active' => '1',
            'original_color' => '1',
            'original_font_color' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#ffffff',
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
        ]);

        $form->preProcess();
        $form->buildQuickForm();
        $form->postProcess();

        $modifiedPetition = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->execute()
            ->first();
        self::assertTrue($modifiedPetition['is_active']);
        self::assertNull($modifiedPetition['background_color']);
        self::assertSame('My new additional note text', $modifiedPetition['additional_note']);
        self::assertNull($modifiedPetition['font_color']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessWithPresets()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $petition = $petition['values'][0];
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form->setVar('_submitValues', [
            'is_active' => '1',
            'original_color' => '1',
            'original_font_color' => '0',
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
            'preset_handler' => 'DummyPetitionPresetProviderClass',
            'hide_form_title' => '',
            'send_size_when_embedded' => '',
            'send_size_to_when_embedded' => '*',
            'add_check_all_checkbox' => '',
            'check_all_checkbox_label' => '',
        ]);

        $form->preProcess();
        $form->buildQuickForm();
        $form->postProcess();

        $modifiedPetition = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->execute()
            ->first();
        self::assertSame('#ffffff', $modifiedPetition['background_color']);
        self::assertSame('My default additional note text', $modifiedPetition['additional_note']);
        self::assertSame('#000000', $modifiedPetition['font_color']);
        self::assertSame('default', $modifiedPetition['consent_field_behaviour']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessTransparentBackground()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $petition = $petition['values'][0];
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
    }
}
