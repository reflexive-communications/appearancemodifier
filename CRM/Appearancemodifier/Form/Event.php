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
    }

    /**
     * Set default values
     *
     * @return array
     */
    public function setDefaultValues()
    {
        $modifiedEvent = AppearancemodifierEvent::get()
            ->addWhere('event_id', '=', $this->event['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        // Set defaults
        foreach (self::EVENT_FIELDS as $key) {
            $this->_defaults[$key] = $modifiedEvent[$key];
        }
        if ($modifiedEvent['background_color'] == null) {
            $this->_defaults['original_color'] = 1;
        } elseif ($modifiedEvent['background_color'] === 'transparent') {
            $this->_defaults['transparent_background'] = 1;
            $this->_defaults['background_color'] = null;
        }
        if ($modifiedEvent['font_color'] == null) {
            $this->_defaults['original_font_color'] = 1;
        }
        // consent field behaviour. on case of null,
        // set it based on the consent invert field.
        if ($modifiedEvent['consent_field_behaviour'] == null) {
            $this->_defaults['consent_field_behaviour'] = $modifiedEvent['invert_consent_fields'] == null ? 'default' : 'invert' ;
        }
        $this->_defaults['preset_handler'] = '';
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
        $submitData = [];
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
        if ($this->_submitValues['preset_handler'] !== '') {
            // Handle the invert_consent_field key from the old presets.
            $presets = $this->_submitValues['preset_handler']::getPresets();
            if (!array_key_exists('consent_field_behaviour', $presets)) {
                $preset['consent_field_behaviour'] = (array_key_exists('invert_consent_fields', $presets) && !empty($presets['invert_consent_fields'])) ? 'invert' : 'default';
            }
            $this->saveCustomEvent($preset);
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
}
