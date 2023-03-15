<?php

use Civi\Appearancemodifier\HeadlessTestCase;

class DummyProfilePresetProviderClass
{
    public static function getPresets(): array
    {
        return [
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
    /*
     * It tests the preProcess function.
     */
    public function testPreProcess()
    {
        // Profile
        $profile = \Civi\Api4\UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $form = new CRM_Appearancemodifier_Form_Profile();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        // not existing profile
        $_REQUEST['pid'] = $profile['id'] + 1;
        $_GET['pid'] = $profile['id'] + 1;
        $_POST['pid'] = $profile['id'] + 1;
        self::expectException(CRM_Core_Exception::class);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /*
     * It tests the setDefaultValues function.
     */
    public function testSetDefaultValuesOriginalColor()
    {
        $profile = \Civi\Api4\UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    public function testSetDefaultValuesTransparentColor()
    {
        $profile = \Civi\Api4\UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        \Civi\Api4\AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['transparent_background']);
        self::assertNull($defaults['background_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    public function testSetDefaultValuesConsentFieldBehaviour()
    {
        $profile = \Civi\Api4\UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        \Civi\Api4\AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame('default', $defaults['consent_field_behaviour']);
    }

    /*
     * It tests the buildQuickForm function.
     */
    public function testBuildQuickForm()
    {
        $profile = \Civi\Api4\UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->buildQuickForm(), 'buildQuickForm supposed to be empty.');
    }

    /*
     * It tests the postProcess function.
     */
    public function testPostProcessWithoutPresets()
    {
        $profile = \Civi\Api4\UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
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
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedProfile = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertNull($modifiedProfile['background_color']);
        self::assertSame($_POST['additional_note'], $modifiedProfile['additional_note']);
        self::assertNull($modifiedProfile['font_color']);
    }

    public function testPostProcessWithPresets()
    {
        $profile = \Civi\Api4\UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $_POST['original_color'] = '1';

        $_POST['layout_handler'] = '';
        $_POST['background_color'] = '#ffffff';
        $_POST['additional_note'] = 'My new additional note text';
        $_POST['consent_field_behaviour'] = 'default';
        $_POST['hide_form_labels'] = '';
        $_POST['add_placeholder'] = '';
        $_POST['preset_handler'] = 'DummyProfilePresetProviderClass';
        $_POST['hide_form_title'] = '';
        $_POST['send_size_when_embedded'] = '';
        $_POST['send_size_to_when_embedded'] = '*';
        $_POST['add_check_all_checkbox'] = '';
        $_POST['check_all_checkbox_label'] = '';
        $_POST['base_target_is_the_parent'] = '';
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedProfile = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertSame('#ffffff', $modifiedProfile['background_color']);
        self::assertSame('My default additional note text', $modifiedProfile['additional_note']);
        self::assertSame('#000000', $modifiedProfile['font_color']);
        self::assertSame('default', $modifiedProfile['consent_field_behaviour']);
    }

    public function testPostProcessTransparentBackground()
    {
        $profile = \Civi\Api4\UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $_REQUEST['pid'] = $profile['id'];
        $_GET['pid'] = $profile['id'];
        $_POST['pid'] = $profile['id'];
        $_POST['original_color'] = '0';
        $_POST['transparent_background'] = '1';

        $_POST['layout_handler'] = '';
        $_POST['background_color'] = '#ffffff';
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
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedProfile = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertSame('transparent', $modifiedProfile['background_color']);
        self::assertSame($_POST['additional_note'], $modifiedProfile['additional_note']);
        self::assertNull($modifiedProfile['font_color']);
    }
}
