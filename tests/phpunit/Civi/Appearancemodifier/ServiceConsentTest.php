<?php

namespace Civi\Appearancemodifier;

use Civi\Api4\ActivityContact;
use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\Contact;
use Civi\Api4\UFField;
use Civi\Api4\UFGroup;
use Civi\Consentactivity\Config;
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
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        $form = new CRM_Profile_Form_Edit();
        $contact = Contact::create(false)
            ->addValue('contact_type', 'Individual')
            ->execute()
            ->first();
        $form->setVar('_id', $contact['id']);
        $form->setVar('_gid', $profile['id']);
        $submit = ['custom_'.$customField['id'] => [1 => '1']];
        $form->setVar('_submitValues', $submit);
        $activityContactsBefore = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact['id'])
            ->addWhere('record_type_id', '=', 3)
            ->execute();
        self::assertEmpty(Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $activityContactsAfter = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact['id'])
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
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        // setup conset activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        unset($cfg['custom-field-map']);
        $config->update($cfg);
        $form = new CRM_Profile_Form_Edit();
        $contact = Contact::create(false)
            ->addValue('contact_type', 'Individual')
            ->execute()
            ->first();
        $form->setVar('_id', $contact['id']);
        $form->setVar('_gid', $profile['id']);
        $submit = ['custom_'.$customField['id'] => [1 => '1']];
        $form->setVar('_submitValues', $submit);
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->addValue('custom_settings', ['consentactivity' => ['custom_'.$customField['id'] => '1']])
            ->setLimit(1)
            ->execute()
            ->first();
        $activityContactsBefore = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact['id'])
            ->addWhere('record_type_id', '=', 3)
            ->execute();
        self::assertEmpty(Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $activityContactsAfter = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact['id'])
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
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        $form = new CRM_Profile_Form_Edit();
        $contact = Contact::create(false)
            ->addValue('contact_type', 'Individual')
            ->execute()
            ->first();
        $form->setVar('_id', $contact['id']);
        $form->setVar('_gid', $profile['id']);
        $submit = ['custom_'.$customField['id'] => [1 => '']];
        $form->setVar('_submitValues', $submit);
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->addValue('custom_settings', ['consentactivity' => ['custom_'.$customField['id'] => '1']])
            ->setLimit(1)
            ->execute()
            ->first();
        $activityContactsBefore = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact['id'])
            ->addWhere('record_type_id', '=', 3)
            ->execute();
        self::assertEmpty(Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $activityContactsAfter = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact['id'])
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
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        $form = new CRM_Profile_Form_Edit();
        $contact = Contact::create(false)
            ->addValue('contact_type', 'Individual')
            ->execute()
            ->first();
        $form->setVar('_id', $contact['id']);
        $form->setVar('_gid', $profile['id']);
        $submit = ['custom_'.$customField['id'] => [1 => '1']];
        $form->setVar('_submitValues', $submit);
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->addValue('custom_settings', ['consentactivity' => ['custom_'.$customField['id'] => '1']])
            ->setLimit(1)
            ->execute()
            ->first();
        $activityContactsBefore = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact['id'])
            ->addWhere('record_type_id', '=', 3)
            ->execute();
        self::assertEmpty(Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $activityContactsAfter = ActivityContact::get()
            ->selectRowCount()
            ->addWhere('contact_id', '=', $contact['id'])
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
        // setup conset activity configuration
        $config = new Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $config->update($cfg);
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        $form = new CRM_Profile_Form_Edit();
        $form->setVar('_gid', $profile['id']);
        $expectedContent = "<div><div class=\"crm-section form-item consentactivity\" id=\"editrow-custom_".$customField['id']
            ."\">\n<div class=\"label\"><label>Move me.</label></div>\n<div class=\"content\"><input type=\"checkbox\" id=\"custom_".$customField['id']."_1\"></div>\n</div></div>";
        $content = '<div><div class="crm-section form-item" id="editrow-custom_'.$customField['id']
            .'"><div class="label"><label>Replace me.</label></div><div class="content"><input type="checkbox" id="custom_'.$customField['id'].'_1" /><label>Move me.</label></div></div></div>';
        self::assertEmpty(Service::alterContent($content, Service::PROFILE_TEMPLATES[0], $form));
        self::assertSame($expectedContent, $content, 'Invalid content has been generated template: '.Service::PROFILE_TEMPLATES[0].'. '.$content);
    }
}
