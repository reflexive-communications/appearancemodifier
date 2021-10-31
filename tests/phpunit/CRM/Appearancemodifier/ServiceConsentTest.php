<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Api4\UFGroup;
use Civi\Api4\UFField;
use Civi\Api4\Contact;
use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\ActivityContact;

/**
 * Testcases for Service class with enabled consentactivity extension.
 *
 * @group headless
 */
class CRM_Appearancemodifier_ServiceConsentTest extends CRM_Appearancemodifier_Form_ConsentBase
{
    /**
     * Test the postProcess function.
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
        $config = new CRM_Consentactivity_Config('consentactivity');
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
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $activityContacts = ActivityContact::get()
            ->addWhere('activity.activity_type_id', '=', 1)
            ->addWhere('activity.status_id', '=', 2)
            ->addWhere('contact_id', '=', $contact['id'])
            ->addWhere('record_type_id', '=', 3)
            ->setLimit(25)
            ->execute();
        self::assertCount(0, $activityContacts);
    }
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
        $config = new CRM_Consentactivity_Config('consentactivity');
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
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $activityContacts = ActivityContact::get()
            ->addWhere('activity.activity_type_id', '=', 1)
            ->addWhere('activity.status_id', '=', 2)
            ->addWhere('contact_id', '=', $contact['id'])
            ->addWhere('record_type_id', '=', 3)
            ->setLimit(25)
            ->execute();
        self::assertCount(0, $activityContacts);
    }
    public function testPostProcessValueNotSet()
    {
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new CRM_Consentactivity_Config('consentactivity');
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
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $activityContacts = ActivityContact::get()
            ->addWhere('activity.activity_type_id', '=', 1)
            ->addWhere('activity.status_id', '=', 2)
            ->addWhere('contact_id', '=', $contact['id'])
            ->addWhere('record_type_id', '=', 3)
            ->setLimit(25)
            ->execute();
        self::assertCount(0, $activityContacts);
    }
    public function testPostProcessValueSet()
    {
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new CRM_Consentactivity_Config('consentactivity');
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
        self::assertEmpty(CRM_Appearancemodifier_Service::postProcess(CRM_Profile_Form_Edit::class, $form));
        $activityContacts = ActivityContact::get()
            ->addWhere('activity.activity_type_id', '=', 1)
            ->addWhere('activity.status_id', '=', 2)
            ->addWhere('contact_id', '=', $contact['id'])
            ->addWhere('record_type_id', '=', 3)
            ->setLimit(25)
            ->execute();
        self::assertCount(1, $activityContacts);
    }
}
