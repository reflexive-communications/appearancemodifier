<?php

namespace Civi\Appearancemodifier;

use Civi\Api4\ActivityContact;
use Civi\Api4\AppearancemodifierProfile;
use Civi\Consentactivity\Config;
use Civi\RcBase\ApiWrapper\Create;
use Civi\RcBase\Utils\PHPUnit;
use CRM_Core_Controller_Simple;
use CRM_Profile_Form_Edit;

/**
 * @group headless
 */
class ServiceConsentTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessNoConsentActivitySettingsOnTheForm()
    {
        $contact_id = PHPUnit::createIndividual();
        $customField = parent::createNewCustomField();
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        Create::entity('UFField', [
            'uf_group_id' => $profile_id,
            'field_name' => 'custom_'.$customField['id'],
        ]);
        // setup consent activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);

        $_REQUEST = [
            'gid' => $profile_id,
            'cid' => $contact_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller_Simple('CRM_Profile_Form_Edit', 'Create Profile');
        $form->preProcess();
        $form->_submitValues = ['custom_'.$customField['id'] => [1 => '1']];

        $activityContactsBefore = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', 3)
            ->execute();

        Service::postProcess(CRM_Profile_Form_Edit::class, $form);

        $activityContactsAfter = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', 3)
            ->execute();
        self::assertCount(count($activityContactsBefore), $activityContactsAfter);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessNoConsentActivitySettings()
    {
        $contact_id = PHPUnit::createIndividual();
        $customField = parent::createNewCustomField();
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        Create::entity('UFField', [
            'uf_group_id' => $profile_id,
            'field_name' => 'custom_'.$customField['id'],
        ]);
        // setup consent activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        unset($cfg['custom-field-map']);
        $config->update($cfg);
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile_id)
            ->addValue('custom_settings', ['consentactivity' => ['custom_'.$customField['id'] => '1']])
            ->setLimit(1)
            ->execute()
            ->first();

        $_REQUEST = [
            'gid' => $profile_id,
            'cid' => $contact_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller_Simple('CRM_Profile_Form_Edit', 'Create Profile');
        $form->preProcess();
        $form->_submitValues = ['custom_'.$customField['id'] => [1 => '1']];

        $activityContactsBefore = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', 3)
            ->execute();

        Service::postProcess(CRM_Profile_Form_Edit::class, $form);

        $activityContactsAfter = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', 3)
            ->execute();
        self::assertCount(count($activityContactsBefore), $activityContactsAfter);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessValueNotSet()
    {
        $contact_id = PHPUnit::createIndividual();
        $customField = parent::createNewCustomField();
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        Create::entity('UFField', [
            'uf_group_id' => $profile_id,
            'field_name' => 'custom_'.$customField['id'],
        ]);
        // setup consent activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile_id)
            ->addValue('custom_settings', ['consentactivity' => ['custom_'.$customField['id'] => '1']])
            ->setLimit(1)
            ->execute()
            ->first();

        $_REQUEST = [
            'gid' => $profile_id,
            'cid' => $contact_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller_Simple('CRM_Profile_Form_Edit', 'Create Profile');
        $form->preProcess();
        $form->_submitValues = ['custom_'.$customField['id'] => [1 => '']];

        $activityContactsBefore = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', 3)
            ->execute();

        Service::postProcess(CRM_Profile_Form_Edit::class, $form);

        $activityContactsAfter = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', 3)
            ->execute();
        self::assertCount(count($activityContactsBefore), $activityContactsAfter);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcessValueSet()
    {
        $contact_id = PHPUnit::createIndividual();
        $customField = parent::createNewCustomField();
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        Create::entity('UFField', [
            'uf_group_id' => $profile_id,
            'field_name' => 'custom_'.$customField['id'],
        ]);
        // setup consent activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile_id)
            ->addValue('custom_settings', ['consentactivity' => ['custom_'.$customField['id'] => '1']])
            ->setLimit(1)
            ->execute()
            ->first();

        $_REQUEST = [
            'gid' => $profile_id,
            'cid' => $contact_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller_Simple('CRM_Profile_Form_Edit', 'Create Profile');
        $form->preProcess();
        $form->_submitValues = ['custom_'.$customField['id'] => [1 => '1']];

        $activityContactsBefore = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', 3)
            ->execute();

        Service::postProcess(CRM_Profile_Form_Edit::class, $form);

        $activityContactsAfter = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact_id)
            ->addWhere('record_type_id', '=', 3)
            ->execute();
        self::assertCount(count($activityContactsBefore) + 1, $activityContactsAfter);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testAlterContentConsentCheckboxes()
    {
        $customField = parent::createNewCustomField();
        $profile_id = Create::entity('UFGroup', ['title' => 'Test profile']);
        Create::entity('UFJoin', [
            'module' => 'Profile',
            'uf_group_id' => $profile_id,
        ]);
        Create::entity('UFField', [
            'uf_group_id' => $profile_id,
            'field_name' => 'custom_'.$customField['id'],
        ]);
        // setup consent activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);

        $_REQUEST = [
            'gid' => $profile_id,
        ];
        $form = new CRM_Profile_Form_Edit();
        $form->controller = new CRM_Core_Controller_Simple('CRM_Profile_Form_Edit', 'Create Profile');
        $form->preProcess();

        $expectedContent = "<div><div class=\"crm-section form-item consentactivity\" id=\"editrow-custom_".$customField['id']
            ."\">\n<div class=\"label\"><label>Move me.</label></div>\n<div class=\"content\"><input type=\"checkbox\" id=\"custom_".$customField['id']."_1\"></div>\n</div></div>";
        $content = '<div><div class="crm-section form-item" id="editrow-custom_'.$customField['id']
            .'"><div class="label"><label>Replace me.</label></div><div class="content"><input type="checkbox" id="custom_'.$customField['id'].'_1" /><label>Move me.</label></div></div></div>';

        Service::alterContent($content, Service::PROFILE_TEMPLATES[0], $form);

        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PROFILE_TEMPLATES[0].'. '.$content);
    }
}
