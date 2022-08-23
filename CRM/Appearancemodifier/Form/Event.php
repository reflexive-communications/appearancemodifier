<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Api4\Event;
use Civi\Api4\AppearancemodifierEvent;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Appearancemodifier_Form_Event extends CRM_Appearancemodifier_Form_AbstractBase
{
    public const DEFAULT_CUSTOM_SETTINGS
        = [
            'hide_form_title' => '',
            'send_size_when_embedded' => '',
            'send_size_to_when_embedded' => '*',
            'add_check_all_checkbox' => '',
            'check_all_checkbox_label' => '',
        ];

    private const EVENT_FIELDS
        = [
            'layout_handler',
            'background_color',
            'consent_field_behaviour',
            'custom_social_box',
            'external_share_url',
            'hide_form_labels',
            'add_placeholder',
            'font_color',
        ];

    // The event, for display some stuff about it on the frontend.
    private $event;

    // The modified event
    private $modifiedEvent;

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        // Get the event id query parameter.
        $eventId = CRM_Utils_Request::retrieve('eid', 'Integer');
        // validate event id.
        $this->event = $this->getEvent($eventId);
        if ($this->event === []) {
            throw new CRM_Core_Exception(E::ts('The selected event seems to be deleted. Id: %1', [1 => $eventId]));
        }
        $this->modifiedEvent = AppearancemodifierEvent::get()
            ->addWhere('event_id', '=', $this->event['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        parent::preProcess();
    }

    /**
     * Set default values
     *
     * @return array
     */
    public function setDefaultValues()
    {
        // Set defaults
        foreach (self::EVENT_FIELDS as $key) {
            $this->_defaults[$key] = $this->modifiedEvent[$key];
        }
        parent::commondDefaultValues($this->modifiedEvent);
        // default for the custom settings.
        parent::customDefaultValues($this->modifiedEvent, self::DEFAULT_CUSTOM_SETTINGS);

        return $this->_defaults;
    }

    /**
     * Build form
     */
    public function buildQuickForm()
    {
        $layoutOptions = [
            'handlers' => [],
            'presets' => [],
        ];
        // Fire hook event.
        Civi::dispatcher()->dispatch(
            "hook_civicrm_appearancemodifierEventSettings",
            Civi\Core\Event\GenericHookEvent::create([
                "options" => &$layoutOptions,
            ])
        );
        $this->add('checkbox', 'custom_social_box', E::ts('Custom social box'), [], false);
        $this->add('text', 'external_share_url', E::ts('External url to share'), [], false);
        parent::commonBuildQuickForm($layoutOptions);
        $this->setTitle(E::ts('Customize %1 event.', [1 => $this->event['title']]));
        parent::buildQuickForm();
    }

    /**
     * Process post data
     */
    public function postProcess()
    {
        $customSettings = $this->modifiedEvent['custom_settings'];
        foreach (self::DEFAULT_CUSTOM_SETTINGS as $key => $v) {
            $customSettings[$key] = $this->_submitValues[$key];
        }
        parent::commonPostProcess(self::EVENT_FIELDS, $customSettings, self::DEFAULT_CUSTOM_SETTINGS);
        CRM_Core_Session::setStatus(E::ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000,]);

        parent::postProcess();
    }

    /**
     * This function is a wrapper for AppearancemodifierEvent.update API call.
     *
     * @param array $data the new values.
     */
    protected function updateCustom(array $data): void
    {
        $modifiedEvent = AppearancemodifierEvent::update()
            ->setLimit(1)
            ->addWhere('event_id', '=', $this->event['id']);
        foreach (self::EVENT_FIELDS as $key) {
            $modifiedEvent = $modifiedEvent->addValue($key, $data[$key]);
        }
        if (array_key_exists('custom_settings', $data)) {
            $modifiedEvent = $modifiedEvent->addValue('custom_settings', $data['custom_settings']);
        }
        $modifiedEvent = $modifiedEvent->execute();
    }

    /*
     * This function is a wrapper for Event.Get API call.
     *
     * @param int $id the event id.
     *
     * @return array the result event or empty array.
     */
    private function getEvent(int $id): array
    {
        $event = Event::get()
            ->addWhere('id', '=', $id)
            ->setLimit(1)
            ->execute();
        if (count($event) === 0) {
            return [];
        }

        return $event->first();
    }

    /*
     * This function gathers the consent custom fields that
     * are present in this petition form.
     */
    protected function consentActivityCustomFields(): void
    {
        // gather the custom fields from the service.
        $consentActivityConfig = new CRM_Consentactivity_Config('consentactivity');
        $consentActivityConfig->load();
        $config = $consentActivityConfig->get();
        if (array_key_exists('custom-field-map', $config)) {
            $map = $config['custom-field-map'];
            $labels = CRM_Consentactivity_Service::customCheckboxFields();
            $uFJoins = \Civi\Api4\UFJoin::get()
                ->addSelect('uf_group_id')
                ->addWhere('module', '=', 'CiviEvent')
                ->addWhere('entity_table', '=', 'civicrm_event')
                ->addWhere('entity_id', '=', $this->event['id'])
                ->execute();
            $profileIds = [];
            foreach ($uFJoins as $profile) {
                $profileIds[] = $profile['uf_group_id'];
            }
            foreach ($map as $rule) {
                // If the current rule field is missing from the profile, continue
                $ufFields = \Civi\Api4\UFField::get()
                    ->addWhere('uf_group_id', 'IN', $profileIds)
                    ->addWhere('field_name', '=', $rule['custom-field-id'])
                    ->setLimit(1)
                    ->execute()
                    ->first();
                if (is_null($ufFields)) {
                    continue;
                }
                // add select of activities with a meaningful label that
                // contains the label as it used in the custom checkbox
                // field select.
                $this->add(
                    'select',
                    'consentactivity_'.$rule['custom-field-id'],
                    E::ts('Activity for %1', [1 => $labels[$rule['custom-field-id']]]),
                    ['' => E::ts('No Activity')] + CRM_Activity_BAO_Activity::buildOptions('activity_type_id', 'get'),
                    false
                );
                $this->consentFieldNames[] = $rule['custom-field-id'];
            }
        }
    }
}
