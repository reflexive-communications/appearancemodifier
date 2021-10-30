<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Api4\UFGroup;
use Civi\Api4\AppearancemodifierProfile;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Appearancemodifier_Form_Profile extends CRM_Core_Form
{
    private const PROFILE_FIELDS = [
        'layout_handler',
        'background_color',
        'additional_note',
        'consent_field_behaviour',
        'hide_form_labels',
        'add_placeholder',
        'font_color',
    ];
    // The uf group, for display some stuff about it on the frontend.
    private $ufGroup;
    // The modified profile
    private $modifiedProfile;
    // consentactivity related configurations
    private $consentFieldNames;

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        // Get the profile id query parameter.
        $ufGroupId = CRM_Utils_Request::retrieve('pid', 'Integer');
        // validate profile id.
        $this->ufGroup = $this->getUfGroup($ufGroupId);
        if ($this->ufGroup === []) {
            throw new CRM_Core_Exception(E::ts('The selected profile seems to be deleted. Id: %1', [1=>$ufGroupId]));
        }
        $this->modifiedProfile = AppearancemodifierProfile::get()
            ->addWhere('uf_group_id', '=', $this->ufGroup['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        $this->consentFieldNames = [];
        $manager = CRM_Extension_System::singleton()->getManager();
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
        foreach (self::PROFILE_FIELDS as $key) {
            $this->_defaults[$key] = $this->modifiedProfile[$key];
        }
        if ($this->modifiedProfile['background_color'] == null) {
            $this->_defaults['original_color'] = 1;
        } elseif ($this->modifiedProfile['background_color'] === 'transparent') {
            $this->_defaults['transparent_background'] = 1;
            $this->_defaults['background_color'] = null;
        }
        if ($this->modifiedProfile['font_color'] == null) {
            $this->_defaults['original_font_color'] = 1;
        }
        // consent field behaviour. on case of null,
        // set it based on the consent invert field.
        if ($this->modifiedProfile['consent_field_behaviour'] == null) {
            $this->_defaults['consent_field_behaviour'] = $this->modifiedProfile['invert_consent_fields'] == null ? 'default' : 'invert' ;
        }
        $this->_defaults['preset_handler'] = '';
        // defaults for the consentactivity extension related config.
        if (count($this->consentFieldNames) > 0 && $this->modifiedProfile['custom_settings'] !== null && isset($this->modifiedProfile['custom_settings']['consentactivity'])) {
            foreach ($this->consentFieldNames as $field) {
                if (isset($this->modifiedProfile['custom_settings']['consentactivity'][$field])) {
                    $this->_defaults['consentactivity_'.$field] = $this->modifiedProfile['custom_settings']['consentactivity'][$field];
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
            "hook_civicrm_appearancemodifierProfileSettings",
            Civi\Core\Event\GenericHookEvent::create([
                "options" => &$layoutOptions,
            ])
        );
        $this->addRadio('preset_handler', E::ts('Presets'), array_merge([''=>E::ts('Custom')], $layoutOptions['presets']), [], null, false);
        $this->add('select', 'layout_handler', E::ts('Form Layout'), array_merge([''=>E::ts('Default')], $layoutOptions['handlers']), false);
        $this->add('color', 'background_color', E::ts('Background Color'), [], false);
        $this->add('wysiwyg', 'additional_note', E::ts('Additional Note Text'), [], false);
        $this->addRadio('consent_field_behaviour', E::ts('Manage Consent Behaviour'), ['default' => E::ts('Default'), 'invert' => E::ts('Invert'), 'apply_on_submit' => E::ts('Submit Implied')], [], null, false);
        $this->add('checkbox', 'original_color', E::ts('Original Background Color'), [], false);
        $this->add('checkbox', 'transparent_background', E::ts('Transparent Background Color'), [], false);
        $this->add('checkbox', 'add_placeholder', E::ts('Add placeholders'), [], false);
        $this->add('checkbox', 'hide_form_labels', E::ts('Hide text input labels'), [], false);
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
        $this->setTitle(E::ts('Customize %1 profile.', [1=>$this->ufGroup['title']]));
        parent::buildQuickForm();
    }

    /**
     * Process post data
     */
    public function postProcess()
    {
        $submitData = [
            'custom_settings' => $this->modifiedProfile['custom_settings'],
        ];
        foreach (self::PROFILE_FIELDS as $key) {
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
            $this->saveCustomProfile($presets);
        } else {
            $this->saveCustomProfile($submitData);
        }
        CRM_Core_Session::setStatus(E::ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000,]);

        parent::postProcess();
    }

    /**
     * This function is a wrapper for AppearancemodifierProfile.update API call.
     *
     * @param array $data the new values.
     */
    private function saveCustomProfile(array $data)
    {
        $modifiedProfile = AppearancemodifierProfile::update()
            ->setLimit(1)
            ->addWhere('uf_group_id', '=', $this->ufGroup['id']);
        foreach (self::PROFILE_FIELDS as $key) {
            $modifiedProfile = $modifiedProfile->addValue($key, $data[$key]);
        }
        if (array_key_exists('custom_settings', $data)) {
            $modifiedProfile = $modifiedProfile->addValue('custom_settings', $data['custom_settings']);
        }
        $modifiedProfile = $modifiedProfile->execute();
    }

    /*
     * This function is a wrapper for UFGroup.Get API call.
     *
     * @param int $id the ufgroup id.
     *
     * @return array the result uf group or empty array.
     */
    private function getUfGroup(int $id): array
    {
        $ufGroup = UFGroup::get()
            ->addWhere('id', '=', $id)
            ->setLimit(1)
            ->execute();
        if (count($ufGroup) === 0) {
            return [];
        }
        return $ufGroup->first();
    }

    /*
     * This function gathers the consent custom fields that
     * are present in this profile.
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
            foreach ($map as $rule) {
                // If the current rule field is missing from the profile, continue
                $ufFields = \Civi\Api4\UFField::get()
                    ->addWhere('uf_group_id', '=', $this->ufGroup['id'])
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
