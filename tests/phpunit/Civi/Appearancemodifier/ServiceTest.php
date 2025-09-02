<?php

namespace Civi\Appearancemodifier;

use Civi\Api4\AppearancemodifierEvent;
use Civi\Api4\AppearancemodifierPetition;
use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\Contact;
use Civi\Api4\Event;
use Civi\Api4\UFGroup;
use Civi\RcBase\ApiWrapper\Create;
use Civi\RcBase\Utils\PHPUnit;
use Civi\Test\TransactionalInterface;
use CRM_Campaign_Form_Petition;
use CRM_Campaign_Form_Petition_Signature;
use CRM_Campaign_Page_Petition_ThankYou;
use CRM_Core_Controller;
use CRM_Core_Controller_Simple;
use CRM_Event_Form_Registration_Confirm;
use CRM_Event_Form_Registration_Register;
use CRM_Event_Page_EventInfo;
use CRM_Profile_Form_Edit;

/**
 * @group headless
 */
class ServiceTest extends HeadlessTestCase implements TransactionalInterface
{
    /**
     * @return void
     */
    public function testLinks()
    {
        $ops = [
            'ufGroup.row.actions' => Service::LINK_PROFILE,
            'petition.dashboard.row' => Service::LINK_PETITION,
            'event.manage.list' => Service::LINK_EVENT,
        ];
        foreach ($ops as $op => $v) {
            $links = [];
            Service::links($op, $links);
            self::assertCount(1, $links);
            self::assertSame($v, $links[0]);
        }
        $links = [];
        $op = 'something.not.handled';
        Service::links($op, $links);
        self::assertCount(0, $links);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPost()
    {
        // UFGroup
        $current = AppearancemodifierProfile::get(false)
            ->execute();
        UFGroup::create()
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $new = AppearancemodifierProfile::get(false)
            ->execute();
        self::assertCount(count($current) + 1, $new);
        // Petition
        $current = AppearancemodifierPetition::get(false)
            ->execute();
        civicrm_api3('Survey', 'create', [
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $new = AppearancemodifierPetition::get(false)
            ->execute();
        self::assertCount(count($current) + 1, $new);
        // Event
        $current = AppearancemodifierEvent::get(false)
            ->execute();
        self::assertCount(0, $current);
        $results = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $new = AppearancemodifierEvent::get(false)
            ->execute();
        self::assertCount(count($current) + 1, $new);
        // not create action
        Event::update(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('id', $results[0]['id'])
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $new = AppearancemodifierEvent::get(false)
            ->execute();
        self::assertCount(count($current) + 1, $new);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessDoesNotUpdateWithoutFields()
    {
        $contact_id = PHPUnit::createIndividual();
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);

        $_REQUEST = [
            'gid' => $profile_id,
            'cid' => $contact_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller_Simple('CRM_Profile_Form_Edit', 'Create Profile');
        $form->preProcess();
        $form->_submitValues = [];

        Service::postProcess(CRM_Profile_Form_Edit::class, $form);

        $updatedContact = Contact::get(false)
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessChangesTheConsentFieldsProfileInvert()
    {
        $contact_id = PHPUnit::createIndividual();
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        $modifiedConfig = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile_id)
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('consent_field_behaviour', 'invert')
            ->execute();

        $_REQUEST = [
            'gid' => $profile_id,
            'cid' => $contact_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller_Simple('CRM_Profile_Form_Edit', 'Create Profile');
        $form->preProcess();
        $form->_submitValues = [
            'is_opt_out' => '',
            'do_not_email' => '',
            'do_not_phone' => '',
        ];

        Service::postProcess(CRM_Profile_Form_Edit::class, $form);

        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertTrue($updatedContact['is_opt_out']);
        self::assertTrue($updatedContact['do_not_email']);
        self::assertTrue($updatedContact['do_not_phone']);

        $form->_submitValues = [
            'is_opt_out' => '1',
            'do_not_email' => '1',
            'do_not_phone' => '1',
        ];

        Service::postProcess(CRM_Profile_Form_Edit::class, $form);

        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertFalse($updatedContact['do_not_email']);
        self::assertFalse($updatedContact['do_not_phone']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessChangesTheConsentFieldsProfileApply()
    {
        $contact_id = PHPUnit::createIndividual(0, [
            'is_opt_out' => true,
            'do_not_phone' => true,
        ]);
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        $modifiedConfig = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile_id)
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('consent_field_behaviour', 'apply_on_submit')
            ->execute();

        $_REQUEST = [
            'gid' => $profile_id,
            'cid' => $contact_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller_Simple('CRM_Profile_Form_Edit', 'Create Profile');
        $form->preProcess();
        $form->_submitValues = [];

        Service::postProcess(CRM_Profile_Form_Edit::class, $form);

        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertFalse($updatedContact['do_not_phone']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessChangesTheConsentFieldsPetitionInvert()
    {
        $contact_id = PHPUnit::createIndividual();
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('consent_field_behaviour', 'invert')
            ->execute();

        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->_surveyId = $result['values'][0]['id'];
        $form->_contactId = $contact_id;

        $form->_submitValues = [
            'is_opt_out' => '',
            'do_not_email' => '',
            'do_not_phone' => '',
        ];
        Service::postProcess(CRM_Campaign_Form_Petition_Signature::class, $form);
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertTrue($updatedContact['is_opt_out']);
        self::assertTrue($updatedContact['do_not_email']);
        self::assertTrue($updatedContact['do_not_phone']);

        $form->_submitValues = [
            'is_opt_out' => '1',
            'do_not_email' => '1',
            'do_not_phone' => '1',
        ];
        Service::postProcess(CRM_Campaign_Form_Petition_Signature::class, $form);
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertFalse($updatedContact['do_not_email']);
        self::assertFalse($updatedContact['do_not_phone']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessChangesTheConsentFieldsPetitionApply()
    {
        $contact_id = PHPUnit::createIndividual(0, [
            'is_opt_out' => true,
            'do_not_phone' => true,
        ]);
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('consent_field_behaviour', 'apply_on_submit')
            ->execute();

        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->_surveyId = $result['values'][0]['id'];
        $form->_contactId = $contact_id;
        $form->_submitValues = [];

        Service::postProcess(CRM_Campaign_Form_Petition_Signature::class, $form);
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertFalse($updatedContact['do_not_phone']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessDoesNothingOnEventRegisterFormWhenTheConfirmScreenEnabled()
    {
        $contact_id = PHPUnit::createIndividual();
        $results = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('consent_field_behaviour', 'invert')
            ->execute();

        $_REQUEST['id'] = $results[0]['id'];
        $form = new CRM_Event_Form_Registration_Register();
        $form->controller = new CRM_Core_Controller();
        $form->_values = ['event' => ['is_confirm_enabled' => 1], 'participant' => ['contact_id' => $contact_id]];
        $form->set('params', [
            'is_opt_out' => '1',
            'do_not_email' => '1',
            'do_not_phone' => '1',
        ]);

        Service::postProcess(CRM_Event_Form_Registration_Register::class, $form);
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertFalse($updatedContact['do_not_email']);
        self::assertFalse($updatedContact['do_not_phone']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessChangesTheConsentFieldsEventRegisterFormWhenTheConfirmScreenDisabled()
    {
        $contact_id = PHPUnit::createIndividual();
        $results = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('consent_field_behaviour', 'invert')
            ->execute();

        $_REQUEST['id'] = $results[0]['id'];
        $form = new CRM_Event_Form_Registration_Register();
        $form->controller = new CRM_Core_Controller();
        $form->_values = ['event' => ['is_confirm_enabled' => 0], 'participant' => ['contact_id' => $contact_id]];

        $form->set('params', [
            'is_opt_out' => '',
            'do_not_email' => '',
            'do_not_phone' => '',
        ]);
        Service::postProcess(CRM_Event_Form_Registration_Register::class, $form);
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertTrue($updatedContact['is_opt_out']);
        self::assertTrue($updatedContact['do_not_email']);
        self::assertTrue($updatedContact['do_not_phone']);

        $form->set('params', [
            'is_opt_out' => '1',
            'do_not_email' => '1',
            'do_not_phone' => '1',
        ]);
        Service::postProcess(CRM_Event_Form_Registration_Register::class, $form);
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertFalse($updatedContact['do_not_email']);
        self::assertFalse($updatedContact['do_not_phone']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessChangesTheConsentFieldsEventInvert()
    {
        $contact_id = PHPUnit::createIndividual();
        $results = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('consent_field_behaviour', 'invert')
            ->execute();

        $_REQUEST['id'] = $results[0]['id'];
        $form = new CRM_Event_Form_Registration_Confirm();
        $form->controller = new CRM_Core_Controller();

        $form->_values = ['participant' => ['contact_id' => $contact_id]];
        $form->set('params', [
            'is_opt_out' => '',
            'do_not_email' => '',
            'do_not_phone' => '',
        ]);
        Service::postProcess(CRM_Event_Form_Registration_Confirm::class, $form);
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertTrue($updatedContact['is_opt_out']);
        self::assertTrue($updatedContact['do_not_email']);
        self::assertTrue($updatedContact['do_not_phone']);

        $form->set('params', [
            'is_opt_out' => '1',
            'do_not_email' => '1',
            'do_not_phone' => '1',
        ]);
        Service::postProcess(CRM_Event_Form_Registration_Confirm::class, $form);
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_email', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertFalse($updatedContact['do_not_email']);
        self::assertFalse($updatedContact['do_not_phone']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessChangesTheConsentFieldsEventApply()
    {
        $contact_id = PHPUnit::createIndividual(0, [
            'is_opt_out' => true,
            'do_not_phone' => true,
        ]);
        $results = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('consent_field_behaviour', 'apply_on_submit')
            ->execute();

        $_REQUEST['id'] = $results[0]['id'];
        $form = new CRM_Event_Form_Registration_Confirm();
        $form->controller = new CRM_Core_Controller();
        $form->_values = ['participant' => ['contact_id' => $contact_id]];
        $form->set('params', []);

        Service::postProcess(CRM_Event_Form_Registration_Confirm::class, $form);
        $updatedContact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_phone')
            ->addWhere('id', '=', $contact_id)
            ->execute()
            ->first();
        self::assertFalse($updatedContact['is_opt_out']);
        self::assertFalse($updatedContact['do_not_phone']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentDoesNothingWhenTheContentIsNotRelevant()
    {
        $tplName = 'other-template';
        $content = '<div class="message help">MyText</div>';
        $origContent = $content;
        $form = new CRM_Campaign_Form_Petition();
        Service::alterContent($content, $tplName, $form);
        self::assertSame($origContent, $content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentProfileAddsPlaceholdersToTextareaWithFlag()
    {
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $profile_id)
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->execute();

        $_REQUEST = [
            'gid' => $profile_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller();
        $form->preProcess();

        $expectedContent =
            "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label\">This is the first</div>\n<div class=\"content\"><textarea placeholder=\"This is the first\"></textarea></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label\">This is the second</div>\n<div class=\"content\"><textarea placeholder=\"This is the second\"></textarea></div>\n</div>\n</div>";
        $content =
            '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><textarea></textarea></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><textarea></textarea></div></div></div>';

        Service::alterContent($content, Service::PROFILE_TEMPLATES[0], $form);

        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PROFILE_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentProfileAddsPlaceholdersToTextInputsWithFlag()
    {
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $profile_id)
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->execute();

        $_REQUEST = [
            'gid' => $profile_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller();
        $form->preProcess();

        $expectedContent =
            "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content =
            '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';

        Service::alterContent($content, Service::PROFILE_TEMPLATES[0], $form);

        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PROFILE_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentProfileHiddenClassWithFlag()
    {
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $profile_id)
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->execute();

        $_REQUEST = [
            'gid' => $profile_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller();
        $form->preProcess();

        $expectedContent =
            "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content =
            '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';

        Service::alterContent($content, Service::PROFILE_TEMPLATES[0], $form);

        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PROFILE_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentPetitionHiddenClassWithFlag()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->_surveyId = $result['values'][0]['id'];
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->execute();
        $expectedContent =
            "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content =
            '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';
        Service::alterContent($content, Service::PETITION_TEMPLATES[0], $form);
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PETITION_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentEventHiddenClassWithFlag()
    {
        $results = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();

        $_REQUEST['id'] = $results[0]['id'];
        $form = new CRM_Event_Form_Registration_Confirm();
        $form->controller = new CRM_Core_Controller();

        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->execute();
        $expectedContent =
            "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content =
            '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';
        Service::alterContent($content, Service::EVENT_TEMPLATES[1], $form);
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::EVENT_TEMPLATES[1].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentPetitionThankYouHiddenClassWithFlag()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $form = new CRM_Campaign_Page_Petition_ThankYou();
        $form->assign('survey_id', $result['values'][0]['id']);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->execute();
        $expectedContent =
            "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n</div>";
        $content =
            '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div></div>';
        Service::alterContent($content, Service::PETITION_TEMPLATES[1], $form);
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PETITION_TEMPLATES[1].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentPetitionMessage()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->_surveyId = $result['values'][0]['id'];
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        $defaultMessage = 'My default message.';
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', $defaultMessage)
            ->execute();
        $expectedContent = "<div><div class=\"crm-petition-activity-profile\">\n<textarea>".$defaultMessage."</textarea><textarea></textarea>\n</div></div>";
        $content = '<div><div class="crm-petition-activity-profile"><textarea></textarea><textarea></textarea></div></div>';
        Service::alterContent($content, Service::PETITION_TEMPLATES[0], $form);
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PETITION_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentPetitionMessageDisabled()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->_surveyId = $result['values'][0]['id'];
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        $defaultMessage = 'My default message.';
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', $defaultMessage)
            ->addValue('custom_settings', ['hide_form_title' => '1', 'disable_petition_message_edit' => '1', 'send_size_when_embedded' => '1', 'send_size_to_when_embedded' => '*'])
            ->execute();
        $expectedContent = "<div><div class=\"crm-petition-activity-profile\">\n<textarea disabled>".$defaultMessage."</textarea><textarea></textarea>\n</div></div>";
        $content = '<div><div class="crm-petition-activity-profile"><textarea></textarea><textarea></textarea></div></div>';
        Service::alterContent($content, Service::PETITION_TEMPLATES[0], $form);
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PETITION_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentPetitionCustomSocialContainerBox()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->_surveyId = $result['values'][0]['id'];
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        $defaultMessage = 'My default message.';
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', $defaultMessage)
            ->addValue('custom_social_box', 1)
            ->execute();
        $expectedContent =
            "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a onclick=\"console.log('fb')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a onclick=\"console.log('tw')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content =
            '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        Service::alterContent($content, Service::PETITION_TEMPLATES[0], $form);
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PETITION_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentPetitionCustomSocialContainerBoxExternalShareUrl()
    {
        $petitionTitle = 'Some title';
        $externalUrl = 'https://www.internet.com/myarticle.html';
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => $petitionTitle,
            'activity_type_id' => 'Petition',
        ]);
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', 'My default message.')
            ->addValue('custom_social_box', 1)
            ->addValue('external_share_url', $externalUrl)
            ->execute();

        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->_surveyId = $result['values'][0]['id'];

        $expectedContent =
            "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a onclick=\"window.open('https://facebook.com/sharer/sharer.php?u="
            .urlencode($externalUrl)
            ."', '_blank')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a onclick=\"window.open('https://twitter.com/intent/tweet?url="
            .urlencode($externalUrl).'&amp;text='.$petitionTitle
            ."', '_blank')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content =
            '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        Service::alterContent($content, Service::PETITION_TEMPLATES[1], $form);
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PETITION_TEMPLATES[1].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentEventCustomSocialContainerBox()
    {
        $results = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();

        $_REQUEST['id'] = $results[0]['id'];
        $form = new CRM_Event_Form_Registration_Confirm();
        $form->controller = new CRM_Core_Controller();

        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        $defaultMessage = 'My default message.';
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('petition_message', $defaultMessage)
            ->addValue('custom_social_box', 1)
            ->execute();
        $expectedContent =
            "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a onclick=\"console.log('fb')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a onclick=\"console.log('tw')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content =
            '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';
        Service::alterContent($content, Service::EVENT_TEMPLATES[2], $form);
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::EVENT_TEMPLATES[2].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentEventCustomSocialContainerBoxThankYouPage()
    {
        $results = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('custom_social_box', 1)
            ->execute();

        $_REQUEST['id'] = $results[0]['id'];
        $page = new CRM_Event_Page_EventInfo();

        $expectedContent =
            "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a onclick=\"console.log('fb')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a onclick=\"console.log('tw')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content =
            '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';

        Service::alterContent($content, Service::EVENT_TEMPLATES[0], $page);

        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::EVENT_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentEventCustomSocialContainerBoxExternalUrl()
    {
        $externalUrl = 'https://www.internet.com/myarticle.html';
        $eventTitle = 'Test event title';
        $results = Event::create(false)
            ->addValue('title', $eventTitle)
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('custom_social_box', 1)
            ->addValue('external_share_url', $externalUrl)
            ->execute();

        $_REQUEST['id'] = $results[0]['id'];
        $page = new CRM_Event_Page_EventInfo();

        $expectedContent =
            "<div id=\"crm-main-content-wrapper\"><div class=\"crm-section crm-socialnetwork\">\n<h2>Please share it</h2>\n<div class=\"appearancemodifier-social-block\">\n<div class=\"social-media-icon\"><a onclick=\"window.open('https://facebook.com/sharer/sharer.php?u="
            .urlencode($externalUrl)
            ."', '_blank')\" target=\"_blank\" title=\"Share on Facebook\"><div><i aria-hidden=\"true\" class=\"crm-i fa-facebook\"></i></div></a></div>\n<div class=\"social-media-icon\"><a onclick=\"window.open('https://twitter.com/intent/tweet?url="
            .urlencode($externalUrl).'&amp;text='.$eventTitle
            ."', '_blank')\" target=\"_blank\" title=\"Share on Twitter\"><div><i aria-hidden=\"true\" class=\"crm-i fa-twitter\"></i></div></a></div>\n</div>\n</div></div>";
        $content =
            '<div id="crm-main-content-wrapper"><div class="crm-socialnetwork"><button id="crm-tw" onclick="console.log(\'tw\')"></button><button id="crm-fb" onclick="console.log(\'fb\')"></button></div></div>';

        Service::alterContent($content, Service::EVENT_TEMPLATES[0], $page);

        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::EVENT_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentProfileCheckAllCheckbox()
    {
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        $modifiedConfig = AppearancemodifierProfile::get(false)
            ->addWhere('id', '=', $profile_id)
            ->execute()
            ->first();
        $customSettings = $modifiedConfig['custom_settings'];
        $customSettings['add_check_all_checkbox'] = '1';
        $customSettings['check_all_checkbox_label'] = 'Check All With Me.';
        AppearancemodifierProfile::update(false)
            ->addWhere('id', '=', $profile_id)
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('custom_settings', $customSettings)
            ->execute();

        $_REQUEST = [
            'gid' => $profile_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller();
        $form->preProcess();

        $expectedContent =
            "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n<div id=\"check-all-checkbox\"><div class=\"crm-section form-item\">\n<div class=\"label\"><label for=\"check-all-checkbox-item\">Check All With Me.</label></div>\n<div class=\"edit-value content\"><input class=\"crm-form-checkbox\" type=\"checkbox\" onclick=\"checkAllCheckboxClickHandler(this)\" id=\"check-all-checkbox-item\"></div>\n<div class=\"clear\"></div>\n</div></div>\n<div class=\"crm-section form-item\">\n<div class=\"label\">This is the checkbox</div>\n<div class=\"content\"><input type=\"checkbox\"></div>\n</div>\n</div>";
        $content =
            '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"><div class="label">This is the checkbox</div><div class="content"><input type="checkbox" /></div></div></div>';

        Service::alterContent($content, Service::PROFILE_TEMPLATES[0], $form);

        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PROFILE_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentPetitionCheckAllCheckbox()
    {
        $result = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $form = new CRM_Campaign_Form_Petition_Signature();
        $form->_surveyId = $result['values'][0]['id'];
        $modifiedConfig = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $result['values'][0]['id'])
            ->execute()
            ->first();
        $customSettings = $modifiedConfig['custom_settings'];
        $customSettings['add_check_all_checkbox'] = '1';
        $customSettings['check_all_checkbox_label'] = 'Check All With Me.';
        AppearancemodifierPetition::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('custom_settings', $customSettings)
            ->execute();
        $expectedContent =
            "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n<div id=\"check-all-checkbox\"><div class=\"crm-section form-item\">\n<div class=\"label\"><label for=\"check-all-checkbox-item\">Check All With Me.</label></div>\n<div class=\"edit-value content\"><input class=\"crm-form-checkbox\" type=\"checkbox\" onclick=\"checkAllCheckboxClickHandler(this)\" id=\"check-all-checkbox-item\"></div>\n<div class=\"clear\"></div>\n</div></div>\n<div class=\"crm-section form-item\">\n<div class=\"label\">This is the checkbox</div>\n<div class=\"content\"><input type=\"checkbox\"></div>\n</div>\n</div>";
        $content =
            '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"><div class="label">This is the checkbox</div><div class="content"><input type="checkbox" /></div></div></div>';
        Service::alterContent($content, Service::PETITION_TEMPLATES[0], $form);
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PETITION_TEMPLATES[0].'. '.$content);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentEventCheckAllCheckbox()
    {
        $results = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        $modifiedConfig = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $results[0]['id'])
            ->execute()
            ->first();
        $customSettings = $modifiedConfig['custom_settings'];
        $customSettings['add_check_all_checkbox'] = '1';
        $customSettings['check_all_checkbox_label'] = 'Check All With Me.';
        AppearancemodifierEvent::update(false)
            ->addWhere('id', '=', $modifiedConfig['id'])
            ->addValue('is_active', true)
            ->addValue('layout_handler', LayoutImplementation::class)
            ->addValue('add_placeholder', 1)
            ->addValue('hide_form_labels', 1)
            ->addValue('custom_settings', $customSettings)
            ->execute();

        $_REQUEST['id'] = $results[0]['id'];
        $page = new CRM_Event_Page_EventInfo();

        $expectedContent =
            "<div>\n<div class=\"crm-section form-item\">\n<div class=\"label hidden-node\">This is the first</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the first\"></div>\n</div>\n<div class=\"crm-section form-item\"> <div class=\"label hidden-node\">This is the second</div>\n<div class=\"content\"><input type=\"text\" placeholder=\"This is the second\"></div>\n</div>\n<div id=\"check-all-checkbox\"><div class=\"crm-section form-item\">\n<div class=\"label\"><label for=\"check-all-checkbox-item\">Check All With Me.</label></div>\n<div class=\"edit-value content\"><input class=\"crm-form-checkbox\" type=\"checkbox\" onclick=\"checkAllCheckboxClickHandler(this)\" id=\"check-all-checkbox-item\"></div>\n<div class=\"clear\"></div>\n</div></div>\n<div class=\"crm-section form-item\">\n<div class=\"label\">This is the checkbox</div>\n<div class=\"content\"><input type=\"checkbox\"></div>\n</div>\n</div>";
        $content =
            '<div><div class="crm-section form-item"><div class="label">This is the first</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"> <div class="label">This is the second</div><div class="content"><input type="text" /></div></div><div class="crm-section form-item"><div class="label">This is the checkbox</div><div class="content"><input type="checkbox" /></div></div></div>';

        Service::alterContent($content, Service::EVENT_TEMPLATES[0], $page);

        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::EVENT_TEMPLATES[0].'. '.$content);
    }
}
