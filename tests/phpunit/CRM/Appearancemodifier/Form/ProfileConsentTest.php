<?php

use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\UFField;
use Civi\Api4\UFGroup;
use Civi\Appearancemodifier\HeadlessTestCase;

/**
 * @group headless
 */
class CRM_Appearancemodifier_Form_ProfileConsentTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
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
        $form = new CRM_Appearancemodifier_Form_Profile();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesNoConfig()
    {
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
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesConfig()
    {
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
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->addValue('custom_settings', ['consentactivity' => ['custom_'.$customField['id'] => '1']])
            ->setLimit(1)
            ->execute()
            ->first();
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testBuildQuickForm()
    {
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
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->buildQuickForm(), 'buildQuickForm supposed to be empty.');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcess()
    {
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
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $_POST['original_color'] = '1';
        $_POST['original_font_color'] = '1';

        $_POST['layout_handler'] = '';
        $_POST['background_color'] = '#ffffff';
        $_POST['font_color'] = '#ffffff';
        $_POST['additional_note'] = 'My new additional note text';
        $_POST['consent_field_behaviour'] = 'default';
        $_POST['hide_form_labels'] = '';
        $_POST['add_placeholder'] = '';
        $_POST['preset_handler'] = '';
        $_POST['hide_form_title'] = '';
        $_POST['send_size_when_embedded'] = '';
        $_POST['send_size_to_when_embedded'] = '*';
        $_POST['add_check_all_checkbox'] = '';
        $_POST['check_all_checkbox_label'] = '';
        $_POST['base_target_is_the_parent'] = '';
        $_POST['consentactivity_custom_'.$customField['id']] = '1';
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedProfile = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertNull($modifiedProfile['background_color']);
        self::assertSame($_POST['additional_note'], $modifiedProfile['additional_note']);
        self::assertNull($modifiedProfile['font_color']);
        self::assertTrue(array_key_exists('custom_settings', $modifiedProfile));
        self::assertTrue(array_key_exists('consentactivity', $modifiedProfile['custom_settings']));
        self::assertSame('1', $modifiedProfile['custom_settings']['consentactivity']['custom_'.$customField['id']]);
    }
}
