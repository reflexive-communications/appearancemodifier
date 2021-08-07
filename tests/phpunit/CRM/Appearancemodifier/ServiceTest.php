<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\AppearancemodifierPetition;
use Civi\Api4\AppearancemodifierEvent;
use Civi\Api4\UFGroup;
use Civi\Api4\Contact;

/**
 * This calss could be used for testing the processes.
 */
class LayoutImplementationTest extends CRM_Appearancemodifier_AbstractLayout
{
    public function setStyleSheets(): void
    {
    }
    public function alterContent(&$content): void
    {
    }
}
/**
 * Testcases for Service class.
 *
 * @group headless
 */
class CRM_Appearancemodifier_ServiceTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
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
     * It tests the alterTemplateFile function.
     */
    public function testAlterTemplateFile()
    {
        // mapped files
        foreach (CRM_Appearancemodifier_Service::TEMPLATE_MAP as $original => $mapped) {
            self::assertEmpty(CRM_Appearancemodifier_Service::alterTemplateFile($original));
            self::assertSame($mapped, $original);
        }
        // not mapped file
        $notMappedTemplate = 'not/mapped/template/file.tpl';
        $template = $notMappedTemplate;
        self::assertEmpty(CRM_Appearancemodifier_Service::alterTemplateFile($template));
        self::assertSame($notMappedTemplate, $template);
    }

    /*
     * It tests the links function.
     */
    public function testLinks()
    {
        $ops = [
            'ufGroup.row.actions' => CRM_Appearancemodifier_Service::LINK_PROFILE,
            'petition.dashboard.row' => CRM_Appearancemodifier_Service::LINK_PETITION,
            'event.manage.list' => CRM_Appearancemodifier_Service::LINK_EVENT,
        ];
        foreach ($ops as $op => $v) {
            $links = [];
            CRM_Appearancemodifier_Service::links($op, $links);
            self::assertCount(1, $links);
            self::assertSame($v, $links[0]);
        }
        $links = [];
        $op = 'something.not.handled';
        CRM_Appearancemodifier_Service::links($op, $links);
        self::assertCount(0, $links);
    }

    /*
     * It tests the pageRun function.
     */
    public function testPost()
    {
        // UFGroup
        $current = AppearancemodifierProfile::get(false)
                ->execute();
        $profile = UFGroup::create()
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $new = AppearancemodifierProfile::get(false)
                ->execute();
        self::assertCount(count($current)+1, $new);
        // Petition
        $current = AppearancemodifierPetition::get(false)
                ->execute();
        $result = civicrm_api3('Survey', 'create', [
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $new = AppearancemodifierPetition::get(false)
                ->execute();
        self::assertCount(count($current)+1, $new);
        // Event
        $current = AppearancemodifierEvent::get(false)
                ->execute();
        self::assertCount(0, $current);
        $results = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $new = AppearancemodifierEvent::get(false)
                ->execute();
        self::assertCount(count($current)+1, $new);
        // not create action
        $results = \Civi\Api4\Event::update(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('id', $results[0]['id'])
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $new = AppearancemodifierEvent::get(false)
            ->execute();
        self::assertCount(count($current)+1, $new);
    }

    /*
     * It tests the pageRun function.
     */
    public function testPageRun()
    {
        // petition thankyou
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        self::assertCount(1, $result['values'], 'Invalid count. '.var_export($result, true));
        $page = new CRM_Campaign_Page_Petition_ThankYou();
        $page->setVar('petition', ['id' => $result['values'][0]['id']]);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->execute();
        self::assertEmpty(CRM_Appearancemodifier_Service::pageRun($page));
        // event info
        $results = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $page = new CRM_Event_Page_EventInfo();
        $page->setVar('_id', $results[0]['id']);
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->execute();
        self::assertEmpty(CRM_Appearancemodifier_Service::pageRun($page));
    }

    /*
     * It tests the buildProfile function.
     */
    public function testBuildProfile()
    {
        $profileName = 'test_ufgroup_name';
        $profile = UFGroup::create()
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('name', $profileName)
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $modifiedConfig = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('hide_form_labels', 1)
            ->execute();
        self::assertEmpty(CRM_Appearancemodifier_Service::buildProfile($profileName));
    }

    /*
     * It tests the buildForm function.
     */
    public function testBuildForm()
    {
        // petition
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->setVar('_surveyId', $result['values'][0]['id']);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('hide_form_labels', 1)
            ->execute();
        self::assertEmpty(CRM_Appearancemodifier_Service::buildForm(CRM_Campaign_Form_Petition_Signature::class, $form));
        // event
        $results = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $form = new CRM_Event_Form_Registration_Register();
        $form->setVar('_eventId', $results[0]['id']);
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('hide_form_labels', 1)
            ->execute();
        self::assertEmpty(CRM_Appearancemodifier_Service::buildForm(CRM_Event_Form_Registration_Register::class, $form));
    }

    /**
     * Test the postProcess function.
     */
    public function testPostProcessDoesNothingWhenTheFormIsIrrelevant()
    {
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess('irrelevant-form-name', new CRM_Profile_Form_Edit()));
    }
    public function testPostProcessDoesNotUpdateWithoutFields()
    {
        $profile = UFGroup::create()
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $form = new CRM_Profile_Form_Edit();
        $contact = Contact::create(false)
            ->addValue('contact_type', 'Individual')
            ->execute()
            ->first();
        $form->setVar('_id', $contact['id']);
        $form->setVar('_gid', $profile['id']);
        $submit = [];
        $form->setVar('_submitValues', $submit);
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $updatedContact = Contact::get(false)
            ->addWhere('id', '=', $contact['id'])
            ->execute()
            ->first();
        self::assertSame($contact['is_opt_out'], $updatedContact['is_opt_out']);
    }
    public function testPostProcessChangesTheConsentFieldsProfile()
    {
        $profile = UFGroup::create()
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $form = new CRM_Profile_Form_Edit();
        $contact = Contact::create(false)
            ->addValue('contact_type', 'Individual')
            ->addValue('is_opt_out', false)
            ->addValue('do_not_email', false)
            ->addValue('do_not_phone', false)
            ->execute()
            ->first();
        $form->setVar('_id', $contact['id']);
        $form->setVar('_gid', $profile['id']);
        $submit = [
            'is_opt_out' => '',
            'do_not_email' => '',
            'do_not_phone' => '',
        ];
        $form->setVar('_submitValues', $submit);
        $modifiedConfig = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('invert_consent_fields', 1)
            ->execute();
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact['id'])
            ->execute()
            ->first();
        self::assertTrue($updatedContact['is_opt_out']);
        self::assertTrue($updatedContact['do_not_email']);
        self::assertTrue($updatedContact['do_not_phone']);
        $submit = [
            'is_opt_out' => '1',
            'do_not_email' => '1',
            'do_not_phone' => '1',
        ];
        $form->setVar('_submitValues', $submit);
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact['id'])
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertTrue(is_null($updatedContact['do_not_email']));
        self::assertTrue(is_null($updatedContact['do_not_phone']));
    }
    public function testPostProcessChangesTheConsentFieldsPetition()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->setVar('_surveyId', $result['values'][0]['id']);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('invert_consent_fields', 1)
            ->execute();
        $contact = Contact::create(false)
            ->addValue('contact_type', 'Individual')
            ->addValue('is_opt_out', false)
            ->addValue('do_not_email', false)
            ->addValue('do_not_phone', false)
            ->execute()
            ->first();
        $form->setVar('_contactId', $contact['id']);
        $submit = [
            'is_opt_out' => '',
            'do_not_email' => '',
            'do_not_phone' => '',
        ];
        $form->setVar('_submitValues', $submit);
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Campaign_Form_Petition_Signature::class, $form));
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact['id'])
            ->execute()
            ->first();
        self::assertTrue($updatedContact['is_opt_out']);
        self::assertTrue($updatedContact['do_not_email']);
        self::assertTrue($updatedContact['do_not_phone']);
        $submit = [
            'is_opt_out' => '1',
            'do_not_email' => '1',
            'do_not_phone' => '1',
        ];
        $form->setVar('_submitValues', $submit);
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Campaign_Form_Petition_Signature::class, $form));
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact['id'])
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertTrue(is_null($updatedContact['do_not_email']));
        self::assertTrue(is_null($updatedContact['do_not_phone']));
    }
    public function testPostProcessChangesTheConsentFieldsEvent()
    {
        $results = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $form = new CRM_Event_Form_Registration_Confirm();
        $form->setVar('_eventId', $results[0]['id']);
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('invert_consent_fields', 1)
            ->execute();
        $contact = Contact::create(false)
            ->addValue('contact_type', 'Individual')
            ->addValue('is_opt_out', false)
            ->addValue('do_not_email', false)
            ->addValue('do_not_phone', false)
            ->execute()
            ->first();
        $form->setVar('_values', ['participant' => ['contact_id' => $contact['id']]]);
        $submit = [
            'is_opt_out' => '',
            'do_not_email' => '',
            'do_not_phone' => '',
        ];
        $form->setVar('_params', $submit);
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Event_Form_Registration_Confirm::class, $form));
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact['id'])
            ->execute()
            ->first();
        self::assertTrue($updatedContact['is_opt_out']);
        self::assertTrue($updatedContact['do_not_email']);
        self::assertTrue($updatedContact['do_not_phone']);
        $submit = [
            'is_opt_out' => '1',
            'do_not_email' => '1',
            'do_not_phone' => '1',
        ];
        $form->setVar('_params', $submit);
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Event_Form_Registration_Confirm::class, $form));
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact['id'])
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertTrue(is_null($updatedContact['do_not_email']));
        self::assertTrue(is_null($updatedContact['do_not_phone']));
    }

    /**
     * Test the alterContent function.
     */
    public function testAlterContentDoesNothingWhenTheContentIsNotRelevant()
    {
        $tplName = 'other-template';
        $content = '<div class="message help">MyText</div>';
        $origContent = $content;
        $form = new CRM_Campaign_Form_Petition();
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, $tplName, $form));
        self::assertSame($origContent, $content);
    }
    public function testAlterContentProfileAddsPlaceholdersToTextareasWithFlag()
    {
        $profile = UFGroup::create()
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $profile['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->execute();
        $form = new CRM_Profile_Form_Edit();
        $form->setVar('_gid', $profile['id']);
        $expectedContent = "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label\">This is the first</div>\n<div class=\"content\"><textarea placeholder=\"This is the first\"></textarea></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label\">This is the second</div>\n<div class=\"content\"><textarea placeholder=\"This is the second\"></textarea></div>\n</div>\n</div>";
        $content = '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><textarea></textarea></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><textarea></textarea></div></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::PROFILE_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::PROFILE_TEMPLATES[0].'. '.$content);
    }
    public function testAlterContentProfileAddsPlaceholdersToTextInputsWithFlag()
    {
        $profile = UFGroup::create()
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $profile['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->execute();
        $form = new CRM_Profile_Form_Edit();
        $form->setVar('_gid', $profile['id']);
        $expectedContent = "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content = '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::PROFILE_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::PROFILE_TEMPLATES[0].'. '.$content);
    }
    public function testAlterContentProfileHiddenClassWithFlag()
    {
        $profile = UFGroup::create()
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $profile['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->execute();
        $form = new CRM_Profile_Form_Edit();
        $form->setVar('_gid', $profile['id']);
        $expectedContent = "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content = '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::PROFILE_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::PROFILE_TEMPLATES[0].'. '.$content);
    }
    public function testAlterContentPetitionHiddenClassWithFlag()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->setVar('_surveyId', $result['values'][0]['id']);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->execute();
        $expectedContent = "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content = '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::PETITION_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::PETITION_TEMPLATES[0].'. '.$content);
    }
    public function testAlterContentEventHiddenClassWithFlag()
    {
        $results = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $form = new CRM_Event_Form_Registration_Confirm();
        $form->setVar('_eventId', $results[0]['id']);
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->execute();
        $expectedContent = "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content = '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::EVENT_TEMPLATES[1], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::EVENT_TEMPLATES[1].'. '.$content);
    }
    public function testAlterContentPetitionThankyouHiddenClassWithFlag()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $form = new CRM_Campaign_Page_Petition_ThankYou();
        $form->setVar('petition', ['id'=>$result['values'][0]['id']]);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->execute();
        $expectedContent = "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content = '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::PETITION_TEMPLATES[1], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::PETITION_TEMPLATES[1].'. '.$content);
    }
    public function testAlterContentPetitionMessage()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->setVar('_surveyId', $result['values'][0]['id']);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        $defaultMessage='My default message.';
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', $defaultMessage)
            ->execute();
        $expectedContent = "<div><div class=\"crm-petition-activity-profile\">\n<textarea>".$defaultMessage."</textarea><textarea></textarea>\n</div></div>";
        $content = '<div><div class="crm-petition-activity-profile"><textarea></textarea><textarea></textarea></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::PETITION_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::PETITION_TEMPLATES[0].'. '.$content);
    }
    public function testAlterContentPetitionCustomSocialContainerBox()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->setVar('_surveyId', $result['values'][0]['id']);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        $defaultMessage='My default message.';
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', $defaultMessage)
            ->addValue('custom_social_box', 1)
            ->execute();
        $expectedContent = "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"console.log('fb')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"console.log('tw')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content = '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::PETITION_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::PETITION_TEMPLATES[0].'. '.$content);
    }
    public function testAlterContentPetitionCustomSocialContainerBoxExternalShareUrl()
    {
        $petitionTitle = 'Some title';
        $externalUrl = 'https://www.internet.com/myarticle.html';
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => $petitionTitle,
            'activity_type_id' => "Petition",
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->setVar('petition', ['id' =>$result['values'][0]['id']]);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        $defaultMessage='My default message.';
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', $defaultMessage)
            ->addValue('custom_social_box', 1)
            ->addValue('external_share_url', $externalUrl)
            ->execute();
        $expectedContent = "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"window.open('https://facebook.com/sharer/sharer.php?u=".urlencode($externalUrl)."', '_blank')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"window.open('https://twitter.com/intent/tweet?url=".urlencode($externalUrl)."&amp;text=".$petitionTitle."', '_blank')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content = '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::PETITION_TEMPLATES[1], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::PETITION_TEMPLATES[1].'. '.$content);
    }
    public function testAlterContentEventCustomSocialContainerBox()
    {
        $results = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $form = new CRM_Event_Form_Registration_Confirm();
        $form->setVar('_eventId', $results[0]['id']);
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        $defaultMessage='My default message.';
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', $defaultMessage)
            ->addValue('custom_social_box', 1)
            ->execute();
        $expectedContent = "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"console.log('fb')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"console.log('tw')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content = '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::EVENT_TEMPLATES[2], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::EVENT_TEMPLATES[2].'. '.$content);
    }
    public function testAlterContentEventCustomSocialContainerBoxThankyouPage()
    {
        $results = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $form = new CRM_Event_Page_EventInfo();
        $form->setVar('_id', $results[0]['id']);
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('custom_social_box', 1)
            ->execute();
        $expectedContent = "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"console.log('fb')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"console.log('tw')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content = '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::EVENT_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::EVENT_TEMPLATES[0].'. '.$content);
    }
    public function testAlterContentEventCustomSocialContainerBoxExternalUrl()
    {
        $externalUrl = 'https://www.internet.com/myarticle.html';
        $eventTitle = 'Test event title';
        $results = \Civi\Api4\Event::create(false)
            ->addValue('title', $eventTitle)
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $form = new CRM_Event_Page_EventInfo();
        $form->setVar('_id', $results[0]['id']);
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('custom_social_box', 1)
            ->addValue('external_share_url', $externalUrl)
            ->execute();
        $expectedContent = "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"window.open('https://facebook.com/sharer/sharer.php?u=".urlencode($externalUrl)."', '_blank')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a href=\"#\" onclick=\"window.open('https://twitter.com/intent/tweet?url=".urlencode($externalUrl)."&amp;text=".$eventTitle."', '_blank')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content = '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::EVENT_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::EVENT_TEMPLATES[0].'. '.$content);
    }
    public function testAlterContentEventMissingId()
    {
        $results = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $form = new CRM_Event_Page_EventInfo();
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('custom_social_box', 1)
            ->execute();
        $expectedContent = '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        $content = '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::EVENT_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::EVENT_TEMPLATES[0].'. '.$content);
    }
    public function testAlterContentPetitionMissingId()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        $defaultMessage='My default message.';
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementationTest::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', $defaultMessage)
            ->execute();
        $content = '<div><div class="crm-petition-activity-profile"><textarea></textarea><textarea></textarea></div></div>';
        $expectedContent = $content;
        self::assertEmpty(CRM_Appearancemodifier_Service::alterContent($content, CRM_Appearancemodifier_Service::PETITION_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.CRM_Appearancemodifier_Service::PETITION_TEMPLATES[0].'. '.$content);
    }
}
