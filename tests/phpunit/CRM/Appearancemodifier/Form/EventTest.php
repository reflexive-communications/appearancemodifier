<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

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
 * Testcases for Event Form class.
 *
 * @group headless
 */
class CRM_Appearancemodifier_Form_EventTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
    public function setUpHeadless()
    {
        return \Civi\Test::headless()
            ->install('rc-base')
            ->installMe(__DIR__)
            ->apply();
    }

    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws \CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB and install depended extension
        \Civi\Test::headless()
            ->install('rc-base')
            ->installMe(__DIR__)
            ->apply(true);
    }

    /**
     * Create a clean DB after running tests
     *
     * @throws CRM_Extension_Exception_ParseException
     */
    public static function tearDownAfterClass(): void
    {
        \Civi\Test::headless()
            ->uninstallMe(__DIR__)
            ->uninstall('rc-base')
            ->apply(true);
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

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
        $event = \Civi\Api4\Event::create(false)
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
        $event = \Civi\Api4\Event::create(false)
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
        $event = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        \Civi\Api4\AppearancemodifierEvent::update(false)
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
        $event = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        \Civi\Api4\AppearancemodifierEvent::update(false)
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
        $event = \Civi\Api4\Event::create(false)
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
        $event = \Civi\Api4\Event::create(false)
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
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedEvent = \Civi\Api4\AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $event['id'])
            ->execute()
            ->first();
        self::assertNull($modifiedEvent['background_color']);
        self::assertNull($modifiedEvent['font_color']);
        self::assertSame($_POST['external_share_url'], $modifiedEvent['external_share_url']);
    }
    public function testPostProcessWithPresets()
    {
        $event = \Civi\Api4\Event::create(false)
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
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedEvent = \Civi\Api4\AppearancemodifierEvent::get(false)
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
        $event = \Civi\Api4\Event::create(false)
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
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedEvent = \Civi\Api4\AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $event['id'])
            ->execute()
            ->first();
        self::assertSame('transparent', $modifiedEvent['background_color']);
        self::assertSame($_POST['external_share_url'], $modifiedEvent['external_share_url']);
        self::assertNull($modifiedEvent['font_color']);
    }
}
