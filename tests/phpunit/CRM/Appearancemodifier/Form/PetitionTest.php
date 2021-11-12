<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

class DummyPetitionPresetProviderClass
{
    public static function getPresets(): array
    {
        return [
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
 * Testcases for Petition Form class.
 *
 * @group headless
 */
class CRM_Appearancemodifier_Form_PetitionTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
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
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        // not existing petition
        $_REQUEST['pid'] = $petition['id']+1;
        $_GET['pid'] = $petition['id']+1;
        $_POST['pid'] = $petition['id']+1;
        self::expectException(CRM_Core_Exception::class);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /*
     * It tests the setDefaultValues function.
     */
    public function testSetDefaultValuesOriginalColor()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }
    public function testSetDefaultValuesTransparentColor()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        \Civi\Api4\AppearancemodifierPetition::update(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['transparent_background']);
        self::assertNull($defaults['background_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }
    public function testSetDefaultValuesConsentFieldBehaviour()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        \Civi\Api4\AppearancemodifierPetition::update(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->addValue('background_color', 'transparent')
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame('default', $defaults['consent_field_behaviour']);
    }
    public function testSetDefaultValuesDisabledMessageEdition()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        \Civi\Api4\AppearancemodifierPetition::update(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->addValue('custom_settings', ['disable_petition_message_edit' => '1'])
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame('1', $defaults['disable_petition_message_edit']);
    }

    /*
     * It tests the buildQuickForm function.
     */
    public function testBuildQuickForm()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->buildQuickForm(), 'buildQuickForm supposed to be empty.');
    }

    /*
     * It tests the postProcess function.
     */
    public function testPostProcessWithoutPresets()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form->setVar('_submitValues', [
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
        ]);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedPetition = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->execute()
            ->first();
        self::assertNull($modifiedPetition['background_color']);
        self::assertSame('My new additional note text', $modifiedPetition['additional_note']);
        self::assertNull($modifiedPetition['font_color']);
    }
    public function testPostProcessWithPresets()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
        $form = new CRM_Appearancemodifier_Form_Petition();
        $_REQUEST['pid'] = $petition['id'];
        $_GET['pid'] = $petition['id'];
        $_POST['pid'] = $petition['id'];
        $form->setVar('_submitValues', [
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
        ]);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedPetition = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->execute()
            ->first();
        self::assertSame('#ffffff', $modifiedPetition['background_color']);
        self::assertSame('My default additional note text', $modifiedPetition['additional_note']);
        self::assertSame('#000000', $modifiedPetition['font_color']);
        self::assertSame('default', $modifiedPetition['consent_field_behaviour']);
    }
    public function testPostProcessTransparentBackground()
    {
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $petition = $petition['values'][0];
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
    }
}
