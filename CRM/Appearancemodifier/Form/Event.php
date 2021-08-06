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
        'invert_consent_fields',
        'custom_social_box',
        'external_share_url',
        'hide_form_labels',
        'add_placeholder',
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
            throw new CRM_Core_Exception(ts('The selected event seems to be deleted. Id: %1', [1=>$eventId]));
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
        $this->add('radio', 'preset_handler', ts('Presets'), array_merge([''=>ts('Custom')], $layoutOptions['presets']), false);
        $this->add('select', 'layout_handler', ts('Form Layout'), array_merge([''=>ts('Default')], $layoutOptions)['handlers'], false);
        $this->add('color', 'background_color', ts('Background Color'), [], false);
        $this->add('checkbox', 'invert_consent_fields', ts('Invert Consent Fields'), [], false);
        $this->add('checkbox', 'original_color', ts('Original Background Color'), [], false);
        $this->add('checkbox', 'hide_form_labels', ts('Hide text input labels'), [], false);
        $this->add('checkbox', 'add_placeholder', ts('Add placeholders'), [], false);
        $this->add('checkbox', 'custom_social_box', ts('Custom social box'), [], false);
        $this->add('text', 'external_share_url', ts('External url to share'), [], false);
        // Submit button
        $this->addButtons(
            [
                [
                    'type' => 'done',
                    'name' => ts('Save'),
                    'isDefault' => true,
                ],
                [
                    'type' => 'cancel',
                    'name' => ts('Cancel'),
                ],
            ]
        );
        $this->setTitle(ts('Customize %1 event.', [1=>$this->event['title']]));
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
        }
        $modifiedEvent = AppearancemodifierEvent::update()
            ->setLimit(1)
            ->addWhere('event_id', '=', $this->event['id']);
        foreach (self::EVENT_FIELDS as $key) {
            $modifiedEvent = $modifiedEvent->addValue($key, $submitData[$key]);
        }
        $modifiedEvent = $modifiedEvent->execute();
        CRM_Core_Session::setStatus(ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000,]);

        parent::postProcess();
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
