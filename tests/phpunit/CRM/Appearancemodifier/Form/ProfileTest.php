<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

class DummyProfilePresetProviderClass
{
    public static function getPresets(): array
    {
        return [
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'additional_note' => 'My default additional note text',
            'invert_consent_fields' => '',
            'hide_form_labels' => '',
            'add_placeholder' => '',
        ];
    }
}
/**
 * Testcases for Profile Form class.
 *
 * @group headless
 */
class CRM_Appearancemodifier_Form_ProfileTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
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
        $_REQUEST['pid'] = $profile['id']+1;
        $_GET['pid'] = $profile['id']+1;
        $_POST['pid'] = $profile['id']+1;
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

        $_POST['layout_handler'] = '';
        $_POST['background_color'] = '#ffffff';
        $_POST['additional_note'] = 'My new additional note text';
        $_POST['invert_consent_fields'] = '';
        $_POST['hide_form_labels'] = '';
        $_POST['add_placeholder'] = '';
        $_POST['preset_handler'] = '';
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedProfile = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertNull($modifiedProfile['background_color']);
        self::assertSame($_POST['additional_note'], $modifiedProfile['additional_note']);
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
        $_POST['invert_consent_fields'] = '';
        $_POST['hide_form_labels'] = '';
        $_POST['add_placeholder'] = '';
        $_POST['preset_handler'] = 'DummyProfilePresetProviderClass';
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedProfile = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertSame('#ffffff', $modifiedProfile['background_color']);
        self::assertSame('My default additional note text', $modifiedProfile['additional_note']);
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
        $_POST['invert_consent_fields'] = '';
        $_POST['hide_form_labels'] = '';
        $_POST['add_placeholder'] = '';
        $_POST['preset_handler'] = '';
        $form = new CRM_Appearancemodifier_Form_Profile();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedProfile = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        self::assertSame('transparent', $modifiedProfile['background_color']);
        self::assertSame($_POST['additional_note'], $modifiedProfile['additional_note']);
    }
}
