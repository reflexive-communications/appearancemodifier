<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Api4\Event;
use Civi\Api4\AppearancemodifierEvent;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Appearancemodifier_Form_Event extends CRM_Core_Form
{
    private const EVENT_FIELDS = [
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
    // consentactivity related configurations
    private $consentFieldNames;

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
            throw new CRM_Core_Exception(E::ts('The selected event seems to be deleted. Id: %1', [1=>$eventId]));
        }
        $this->modifiedEvent = AppearancemodifierEvent::get()
            ->addWhere('event_id', '=', $this->event['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        $manager = CRM_Extension_System::singleton()->getManager();
        $this->consentFieldNames = [];
        if ($manager->getStatus('consentactivity') === CRM_Extension_Manager::STATUS_INSTALLED) {
            $this->consentActivityCustomFields();
        }
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
        if ($this->modifiedEvent['background_color'] == null) {
            $this->_defaults['original_color'] = 1;
        } elseif ($this->modifiedEvent['background_color'] === 'transparent') {
            $this->_defaults['transparent_background'] = 1;
            $this->_defaults['background_color'] = null;
        }
        if ($this->modifiedEvent['font_color'] == null) {
            $this->_defaults['original_font_color'] = 1;
        }
        // consent field behaviour. on case of null,
        // set it based on the consent invert field.
        if ($this->modifiedEvent['consent_field_behaviour'] == null) {
            $this->_defaults['consent_field_behaviour'] = $this->modifiedEvent['invert_consent_fields'] == null ? 'default' : 'invert' ;
        }
        $this->_defaults['preset_handler'] = '';
        // defaults for the consentactivity extension related config.
        if (count($this->consentFieldNames) > 0 && $this->modifiedEvent['custom_settings'] !== null && isset($this->modifiedEvent['custom_settings']['consentactivity'])) {
            foreach ($this->consentFieldNames as $field) {
                if (isset($this->modifiedEvent['custom_settings']['consentactivity'][$field])) {
                    $this->_defaults['consentactivity_'.$field] = $this->modifiedEvent['custom_settings']['consentactivity'][$field];
                }
            }
        }
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
        $this->addRadio('preset_handler', E::ts('Presets'), array_merge([''=>E::ts('Custom')], $layoutOptions['presets']), [], null, false);
        $this->add('select', 'layout_handler', E::ts('Form Layout'), array_merge([''=>E::ts('Default')], $layoutOptions['handlers']), false);
        $this->add('color', 'background_color', E::ts('Background Color'), [], false);
        $this->addRadio('consent_field_behaviour', E::ts('Manage Consent Behaviour'), ['default' => E::ts('Default'), 'invert' => E::ts('Invert'), 'apply_on_submit' => E::ts('Submit Implied')], [], null, false);
        $this->add('checkbox', 'original_color', E::ts('Original Background Color'), [], false);
        $this->add('checkbox', 'transparent_background', E::ts('Transparent Background Color'), [], false);
        $this->add('checkbox', 'hide_form_labels', E::ts('Hide text input labels'), [], false);
        $this->add('checkbox', 'add_placeholder', E::ts('Add placeholders'), [], false);
        $this->add('checkbox', 'custom_social_box', E::ts('Custom social box'), [], false);
        $this->add('text', 'external_share_url', E::ts('External url to share'), [], false);
        $this->add('color', 'font_color', E::ts('Font Color'), [], false);
        $this->add('checkbox', 'original_font_color', E::ts('Original Font Color'), [], false);
        // If the consentactivity extension is installed, the custom consent field -> activity mapping has to be provided
        // defaults for the consentactivity extension related config.
        if (count($this->consentFieldNames) > 0) {
            $consentActivityFieldNames = [];
            $labels = CRM_Consentactivity_Service::customCheckboxFields();
            foreach ($this->consentFieldNames as $field) {
                $this->add('select', 'consentactivity_'.$field, E::ts('Activity for %1', [ 1 => $labels[$field]]), [''=>E::ts('No Activity')] + CRM_Activity_BAO_Activity::buildOptions('activity_type_id', 'get'), false);
                $consentActivityFieldNames[] = 'consentactivity_'.$field;
            }
            $this->assign('consentActivityFieldNames', $consentActivityFieldNames);
        }
        // Submit button
        $this->addButtons(
            [
                [
                    'type' => 'done',
                    'name' => E::ts('Save'),
                    'isDefault' => true,
                ],
                [
                    'type' => 'cancel',
                    'name' => E::ts('Cancel'),
                ],
            ]
        );
        $this->setTitle(E::ts('Customize %1 event.', [1=>$this->event['title']]));
        parent::buildQuickForm();
    }

    /**
     * Process post data
     */
    public function postProcess()
    {
        $submitData = [
            'custom_settings' => $this->modifiedEvent['custom_settings'],
        ];
        foreach (self::EVENT_FIELDS as $key) {
            $submitData[$key] = $this->_submitValues[$key];
        }
        if ($this->_submitValues['original_color'] === '1') {
            $submitData['background_color'] = '';
        } elseif ($this->_submitValues['transparent_background'] === '1') {
            $submitData['background_color'] = 'transparent';
        }
        if ($this->_submitValues['original_font_color'] === '1') {
            $submitData['font_color'] = '';
        }
        // consentactivity fields has to be set here.
        if (count($this->consentFieldNames) > 0) {
            $submitData['custom_settings']['consentactivity'] = [];
            foreach ($this->consentFieldNames as $field) {
                $submitData['custom_settings']['consentactivity'][$field] = $this->_submitValues['consentactivity_'.$field];
            }
        }
        if ($this->_submitValues['preset_handler'] !== '') {
            // Handle the invert_consent_field key from the old presets.
            $presets = $this->_submitValues['preset_handler']::getPresets();
            if (!array_key_exists('consent_field_behaviour', $presets)) {
                $presets['consent_field_behaviour'] = (array_key_exists('invert_consent_fields', $presets) && !empty($presets['invert_consent_fields'])) ? 'invert' : 'default';
            }
            if (!array_key_exists('custom_settings', $presets)) {
                $presets['custom_settings'] = [];
            }
            $this->saveCustomEvent($presets);
        } else {
            $this->saveCustomEvent($submitData);
        }
        CRM_Core_Session::setStatus(E::ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000,]);

        parent::postProcess();
    }

    /**
     * This function is a wrapper for AppearancemodifierEvent.update API call.
     *
     * @param array $data the new values.
     */
    private function saveCustomEvent(array $data)
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
    private function consentActivityCustomFields(): void
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
                ->setLimit(25)
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
                $this->add('select', 'consentactivity_'.$rule['custom-field-id'], E::ts('Activity for %1', [ 1 => $labels[$rule['custom-field-id']]]), [''=>E::ts('No Activity')] + CRM_Activity_BAO_Activity::buildOptions('activity_type_id', 'get'), false);
                $this->consentFieldNames[] = $rule['custom-field-id'];
            }
        }
    }
}
