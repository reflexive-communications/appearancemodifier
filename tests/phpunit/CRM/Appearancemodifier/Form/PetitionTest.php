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
            'outro' => 'My default outro text',
            'petition_message' => 'My new petition message text',
            'invert_consent_fields' => '',
            'target_number_of_signers' => '',
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
    public function testSetDefaultValues()
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
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'outro' => 'My new outro text',
            'petition_message' => 'My new petition message text',
            'invert_consent_fields' => '',
            'target_number_of_signers' => '',
            'custom_social_box' => '',
            'external_share_url' => 'my.link.com',
            'hide_form_labels' => '',
            'add_placeholder' => '',
            'preset_handler' => '',
        ]);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedPetition = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->execute()
            ->first();
        self::assertNull($modifiedPetition['background_color']);
        self::assertSame('My new outro text', $modifiedPetition['outro']);
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
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'outro' => 'My new outro text',
            'petition_message' => 'My new petition message text',
            'invert_consent_fields' => '',
            'target_number_of_signers' => '',
            'custom_social_box' => '',
            'external_share_url' => 'my.link.com',
            'hide_form_labels' => '',
            'add_placeholder' => '',
            'preset_handler' => 'DummyPetitionPresetProviderClass',
        ]);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedPetition = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->execute()
            ->first();
        self::assertNull($modifiedPetition['background_color']);
        self::assertSame('My default outro text', $modifiedPetition['outro']);
    }
}
