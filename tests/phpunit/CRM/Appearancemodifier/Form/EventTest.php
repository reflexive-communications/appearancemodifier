<?php

use Civi\Api4\AppearancemodifierEvent;
use Civi\Api4\Event;
use Civi\Appearancemodifier\HeadlessTestCase;

class DummyEventPresetProviderClass
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
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPreProcessMissingEvent()
    {
        $_REQUEST['eid'] = 2;
        $_GET['eid'] = 2;
        $_POST['eid'] = 2;
        $form = new CRM_Appearancemodifier_Form_Event();
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('The selected event seems to be deleted. Id: 2');
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
        $form->preProcess();
        $defaults = $form->setDefaultValues();
        self::assertTrue($defaults['is_active']);
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
        $form->preProcess();
        $defaults = $form->setDefaultValues();
        self::assertTrue($defaults['is_active']);
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
        $values = [
            'original_color' => '1',
            'original_font_color' => '1',
            'is_active' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#ffffff',
            'consent_field_behaviour' => 'default',
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
        ];
        $form->setVar('_submitValues', $values);

        $form->preProcess();
        $form->buildQuickForm();
        $form->postProcess();

        $modifiedEvent = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $event['id'])
            ->execute()
            ->first();
        self::assertTrue($modifiedEvent['is_active']);
        self::assertNull($modifiedEvent['background_color']);
        self::assertNull($modifiedEvent['font_color']);
        self::assertSame($values['external_share_url'], $modifiedEvent['external_share_url']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
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

        $form = new CRM_Appearancemodifier_Form_Event();
        $values = [
            'original_color' => '1',
            'original_font_color' => '0',
            'font_color' => '#000000',
            'is_active' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'consent_field_behaviour' => 'default',
            'custom_social_box' => '',
            'external_share_url' => 'my.link.com',
            'hide_form_labels' => '',
            'add_placeholder' => '',
            'preset_handler' => 'DummyEventPresetProviderClass',
            'hide_form_title' => '',
            'send_size_when_embedded' => '',
            'send_size_to_when_embedded' => '*',
            'add_check_all_checkbox' => '',
            'check_all_checkbox_label' => '',
        ];
        $form->setVar('_submitValues', $values);

        $form->preProcess();
        $form->buildQuickForm();
        $form->postProcess();

        $modifiedEvent = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $event['id'])
            ->execute()
            ->first();
        self::assertTrue($modifiedEvent['is_active']);
        self::assertSame('#ffffff', $modifiedEvent['background_color']);
        self::assertSame('my.updated.link.com', $modifiedEvent['external_share_url']);
        self::assertSame('#000000', $modifiedEvent['font_color']);
        self::assertSame('default', $modifiedEvent['consent_field_behaviour']);
    }
}
