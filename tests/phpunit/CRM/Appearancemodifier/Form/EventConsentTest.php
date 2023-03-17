<?php

use Civi\Api4\AppearancemodifierEvent;
use Civi\Api4\Event;
use Civi\Api4\UFField;
use Civi\Api4\UFGroup;
use Civi\Api4\UFJoin;
use Civi\Appearancemodifier\HeadlessTestCase;

/**
 * @group headless
 */
class CRM_Appearancemodifier_Form_EventConsentTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPreProcess()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        $customField = parent::createNewCustomField();
        $customFieldOther = parent::createNewCustomField();
        // setup conset activity configuration
        $config = new CRM_Consentactivity_Config('consentactivity');
        $config->load();
        $cfg = $config->get();
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customField['id'],
            'consent-field-id' => 'do_not_phone',
            'group-id' => '0',
        ];
        $cfg['custom-field-map'][] = [
            'custom-field-id' => 'custom_'.$customFieldOther['id'],
            'consent-field-id' => 'do_not_email',
            'group-id' => '0',
        ];
        $config->update($cfg);
        UFField::create(false)
            ->addValue('uf_group_id', $profile['id'])
            ->addValue('field_name', 'custom_'.$customField['id'])
            ->execute();
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        UFJoin::create(false)
            ->addValue('module', 'CiviEvent')
            ->addValue('entity_table', 'civicrm_event')
            ->addValue('entity_id', $event['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesNoConfig()
    {
        // Profile
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
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        UFJoin::create(false)
            ->addValue('module', 'CiviEvent')
            ->addValue('entity_table', 'civicrm_event')
            ->addValue('entity_id', $event['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        $defaults = $form->setDefaultValues();
        self::assertSame(1, $defaults['original_color']);
        self::assertSame(1, $defaults['original_font_color']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSetDefaultValuesConfig()
    {
        // Profile
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
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        UFJoin::create(false)
            ->addValue('module', 'CiviEvent')
            ->addValue('entity_table', 'civicrm_event')
            ->addValue('entity_id', $event['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        AppearancemodifierEvent::update(false)
            ->addWhere('event_id', '=', $event['id'])
            ->addValue('custom_settings', ['consentactivity' => ['custom_'.$customField['id'] => '1']])
            ->setLimit(1)
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

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testBuildQuickForm()
    {
        // Profile
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
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        UFJoin::create(false)
            ->addValue('module', 'CiviEvent')
            ->addValue('entity_table', 'civicrm_event')
            ->addValue('entity_id', $event['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $form = new CRM_Appearancemodifier_Form_Event();
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->buildQuickForm(), 'buildQuickForm supposed to be empty.');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostProcess()
    {
        // Profile
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
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        UFJoin::create(false)
            ->addValue('module', 'CiviEvent')
            ->addValue('entity_table', 'civicrm_event')
            ->addValue('entity_id', $event['id'])
            ->addValue('uf_group_id', $profile['id'])
            ->execute();
        $form = new CRM_Appearancemodifier_Form_Event();
        $_REQUEST['eid'] = $event['id'];
        $_GET['eid'] = $event['id'];
        $_POST['eid'] = $event['id'];
        $form->setVar('_submitValues', [
            'original_color' => '0',
            'original_font_color' => '0',
            'transparent_background' => '1',
            'layout_handler' => '',
            'background_color' => '#ffffff',
            'font_color' => '#000000',
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
            'consentactivity_custom_'.$customField['id'] => '1',
        ]);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'postProcess supposed to be empty.');
        $modifiedEvent = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $event['id'])
            ->execute()
            ->first();
        self::assertSame('transparent', $modifiedEvent['background_color']);
        self::assertSame('#000000', $modifiedEvent['font_color']);
        self::assertTrue(array_key_exists('custom_settings', $modifiedEvent));
        self::assertTrue(array_key_exists('consentactivity', $modifiedEvent['custom_settings']));
        self::assertSame('1', $modifiedEvent['custom_settings']['consentactivity']['custom_'.$customField['id']]);
    }
}
