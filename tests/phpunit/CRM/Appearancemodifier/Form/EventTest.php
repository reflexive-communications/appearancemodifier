<?php

use Civi\Api4\AppearancemodifierEvent;
use Civi\Api4\Event;
use Civi\Appearancemodifier\HeadlessTestCase;

class DummyEventPresetProviderClass
{
    public static function getPresets(): array
    {
        return [
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#000000',
            'invert_consent_fields' => '',
            'target_number_of_signers' => '',
            'custom_social_box' => '',
            'external_share_url' => 'my.updated.link.com',
            'hide_form_labels' => '',
            'add_placeholder' => '',
        ];
    }
}

/**
 * @group headless
 */
class CRM_Appearancemodifier_Form_EventTest extends HeadlessTestCase
{
    /*
     * It tests the preProcess function.
     */
    public function testPreProcessMissingEvent()
    {
        $_REQUEST['eid'] = 2;
        $_GET['eid'] = 2;
        $_POST['eid'] = 2;
        $form = new CRM_Appearancemodifier_Form_Event();
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('The selected event seems to be deleted. Id: 2');
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    public function testPreProcessExistingEvent()
    {
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /*
     * It tests the setDefaultValues function.
     */
    public function testSetDefaultValuesOriginalColor()
    {
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    public function testSetDefaultValuesTransparentColor()
    {
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('event_id', '=', $event['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['transparent_background']);
        self::assertNull($defaults['background_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    public function testSetDefaultValuesConsentFieldBehaviour()
    {
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('event_id', '=', $event['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame('default', $defaults['consent_field_behaviour']);
    }

    /*
     * It tests the buildQuickForm function.
     */
    public function testBuildQuickForm()
    {
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->buildQuickForm(), 'buildQuickForm supposed to be empty.');
    }

    /*
     * It tests the postProcess function.
     */
    public function testPostProcessWithoutPresets()
    {
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $_POST['original_color'] = '1';
        $_POST['original_font_color'] = '1';

        $_POST['layout_handler'] = '';
        $_POST['background_color'] = '#ffffff';
        $_POST['font_color'] = '#ffffff';
        $_POST['consent_field_behaviour'] = 'default';
        $_POST['custom_social_box'] = '';
        $_POST['external_share_url'] = 'my.link.com';
        $_POST['hide_form_labels'] = '';
        $_POST['add_placeholder'] = '';
        $_POST['preset_handler'] = '';
        $_POST['hide_form_title'] = '';
        $_POST['send_size_when_embedded'] = '';
        $_POST['send_size_to_when_embedded'] = '*';
        $_POST['add_check_all_checkbox'] = '';
        $_POST['check_all_checkbox_label'] = '';
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedEvent = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $event['id'])
            ->execute()
            ->first();
        self::assertNull($modifiedEvent['background_color']);
        self::assertNull($modifiedEvent['font_color']);
        self::assertSame($_POST['external_share_url'], $modifiedEvent['external_share_url']);
    }

    public function testPostProcessWithPresets()
    {
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $_POST['original_color'] = '1';

        $_POST['layout_handler'] = '';
        $_POST['background_color'] = '#ffffff';
        $_POST['consent_field_behaviour'] = 'default';
        $_POST['custom_social_box'] = '';
        $_POST['external_share_url'] = 'my.link.com';
        $_POST['hide_form_labels'] = '';
        $_POST['add_placeholder'] = '';
        $_POST['preset_handler'] = 'DummyEventPresetProviderClass';
        $_POST['consent_field_behaviour'] = 'default';
        $_POST['hide_form_title'] = '';
        $_POST['send_size_when_embedded'] = '';
        $_POST['send_size_to_when_embedded'] = '*';
        $_POST['add_check_all_checkbox'] = '';
        $_POST['check_all_checkbox_label'] = '';
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedEvent = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $event['id'])
            ->execute()
            ->first();
        self::assertSame('#ffffff', $modifiedEvent['background_color']);
        self::assertSame('my.updated.link.com', $modifiedEvent['external_share_url']);
        self::assertSame('#000000', $modifiedEvent['font_color']);
        self::assertSame('default', $modifiedEvent['consent_field_behaviour']);
    }

    public function testPostProcessTransparentBackground()
    {
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $_POST['original_color'] = '0';
        $_POST['transparent_background'] = '1';

        $_POST['layout_handler'] = '';
        $_POST['background_color'] = '#ffffff';
        $_POST['consent_field_behaviour'] = 'default';
        $_POST['custom_social_box'] = '';
        $_POST['external_share_url'] = 'my.link.com';
        $_POST['hide_form_labels'] = '';
        $_POST['add_placeholder'] = '';
        $_POST['preset_handler'] = '';
        $_POST['hide_form_title'] = '';
        $_POST['send_size_when_embedded'] = '';
        $_POST['send_size_to_when_embedded'] = '*';
        $_POST['add_check_all_checkbox'] = '';
        $_POST['check_all_checkbox_label'] = '';
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedEvent = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $event['id'])
            ->execute()
            ->first();
        self::assertSame('transparent', $modifiedEvent['background_color']);
        self::assertSame($_POST['external_share_url'], $modifiedEvent['external_share_url']);
        self::assertNull($modifiedEvent['font_color']);
    }
}
