<?php

use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\UFGroup;
use Civi\Appearancemodifier\HeadlessTestCase;

class DummyProfilePresetProviderClass
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
            'invert_consent_fields' => '',
            'hide_form_labels' => '',
            'add_placeholder' => '',
        ];
    }
}

/**
 * @group headless
 */
class CRM_Appearancemodifier_Form_ProfileTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPreProcessMissingProfile()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $form = new CRM_Appearancemodifier_Form_Profile();
        $_REQUEST['pid'] = $profile['id'] + 1;
        $_GET['pid'] = $profile['id'] + 1;
        $_POST['pid'] = $profile['id'] + 1;
        self::expectException(CRM_Core_Exception::class);
        $form->preProcess();
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesOriginalColor()
    {
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
        $form->preProcess();
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
    public function testSetDefaultValuesTransparentColor()
    {
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
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
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesConsentFieldBehaviour()
    {
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
        $form->preProcess();
        $defaults = $form->setDefaultValues();
        self::assertSame('default', $defaults['consent_field_behaviour']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessWithoutPresets()
    {
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];

        $form = new CRM_Appearancemodifier_Form_Profile();
        $form->setVar('_submitValues', [
            'is_active' => '1',
            'original_color' => '1',
            'original_font_color' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#ffffff',
            'additional_note' => 'My new additional note text',
            'consent_field_behaviour' => 'default',
            'hide_form_labels' => '',
            'add_placeholder' => '',
            'preset_handler' => '',
            'hide_form_title' => '',
            'send_size_when_embedded' => '',
            'send_size_to_when_embedded' => '*',
            'add_check_all_checkbox' => '',
            'check_all_checkbox_label' => '',
            'base_target_is_the_parent' => '',
        ]);

        $form->preProcess();
        $form->buildQuickForm();
        $form->postProcess();

        $modifiedProfile = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertNull($modifiedProfile['background_color']);
        self::assertSame('My new additional note text', $modifiedProfile['additional_note']);
        self::assertNull($modifiedProfile['font_color']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessWithPresets()
    {
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];

        $form = new CRM_Appearancemodifier_Form_Profile();
        $form->setVar('_submitValues', [
            'is_active' => '1',
            'original_color' => '1',
            'original_font_color' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#ffffff',
            'additional_note' => 'My new additional note text',
            'consent_field_behaviour' => 'default',
            'hide_form_labels' => '',
            'add_placeholder' => '',
            'preset_handler' => 'DummyProfilePresetProviderClass',
            'hide_form_title' => '',
            'send_size_when_embedded' => '',
            'send_size_to_when_embedded' => '*',
            'add_check_all_checkbox' => '',
            'check_all_checkbox_label' => '',
            'base_target_is_the_parent' => '',
        ]);

        $form->preProcess();
        $form->buildQuickForm();
        $form->postProcess();

        $modifiedProfile = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertSame('#ffffff', $modifiedProfile['background_color']);
        self::assertSame('My default additional note text', $modifiedProfile['additional_note']);
        self::assertSame('#000000', $modifiedProfile['font_color']);
        self::assertSame('default', $modifiedProfile['consent_field_behaviour']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessTransparentBackground()
    {
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];

        $form = new CRM_Appearancemodifier_Form_Profile();
        $form->setVar('_submitValues', [
            'is_active' => '1',
            'original_color' => '0',
            'original_font_color' => '1',
            'transparent_background' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#ffffff',
            'additional_note' => 'My new additional note text',
            'consent_field_behaviour' => 'default',
            'hide_form_labels' => '',
            'add_placeholder' => '',
            'preset_handler' => '',
            'hide_form_title' => '',
            'send_size_when_embedded' => '',
            'send_size_to_when_embedded' => '*',
            'add_check_all_checkbox' => '',
            'check_all_checkbox_label' => '',
            'base_target_is_the_parent' => '',
        ]);

        $form->preProcess();
        $form->buildQuickForm();
        $form->postProcess();

        $modifiedProfile = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertSame('transparent', $modifiedProfile['background_color']);
        self::assertSame('My new additional note text', $modifiedProfile['additional_note']);
        self::assertNull($modifiedProfile['font_color']);
    }
}
