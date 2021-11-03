<?php

use CRM_Appearancemodifier_ExtensionUtil as E;

/**
 * Abstract form controller class
 * It handles the common steps across the implementations.
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
abstract class CRM_Appearancemodifier_Form_AbstractBase extends CRM_Core_Form
{
    // consentactivity related configurations
    protected $consentFieldNames;

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        $manager = CRM_Extension_System::singleton()->getManager();
        $this->consentFieldNames = [];
        if ($manager->getStatus('consentactivity') === CRM_Extension_Manager::STATUS_INSTALLED) {
            $this->consentActivityCustomFields();
        }
    }

    /**
     * This function sets the default values for the fields that are present
     * every form implementation.
     *
     * @param array $customFormData the entity data.
     *
     * @throws CRM_Core_Exception
     */
    protected function commondDefaultValues(array $customFormData): void
    {
        if ($customFormData['background_color'] == null) {
            $this->_defaults['original_color'] = 1;
        } elseif ($customFormData['background_color'] === 'transparent') {
            $this->_defaults['transparent_background'] = 1;
            $this->_defaults['background_color'] = null;
        }
        if ($customFormData['font_color'] == null) {
            $this->_defaults['original_font_color'] = 1;
        }
        // consent field behaviour. on case of null,
        // set it based on the consent invert field.
        if ($customFormData['consent_field_behaviour'] == null) {
            $this->_defaults['consent_field_behaviour'] = $customFormData['invert_consent_fields'] == null ? 'default' : 'invert' ;
        }
        $this->_defaults['preset_handler'] = '';
        // defaults for the consentactivity extension related config.
        if (count($this->consentFieldNames) > 0 && $customFormData['custom_settings'] !== null && isset($customFormData['custom_settings']['consentactivity'])) {
            foreach ($this->consentFieldNames as $field) {
                if (isset($customFormData['custom_settings']['consentactivity'][$field])) {
                    $this->_defaults['consentactivity_'.$field] = $customFormData['custom_settings']['consentactivity'][$field];
                }
            }
        }
    }

    /**
     * This function sets the common fields on the quick form.
     *
     * @param array $customOptions the data provided by the hooks.
     *
     * @throws CRM_Core_Exception
     */
    protected function commonBuildQuickForm(array $customOptions): void
    {
        $this->addRadio('preset_handler', E::ts('Presets'), array_merge([''=>E::ts('Custom')], $customOptions['presets']), [], null, false);
        $this->add('select', 'layout_handler', E::ts('Form Layout'), array_merge([''=>E::ts('Default')], $customOptions['handlers']), false);
        $this->add('color', 'background_color', E::ts('Background Color'), [], false);
        $this->addRadio('consent_field_behaviour', E::ts('Manage Consent Behaviour'), ['default' => E::ts('Default'), 'invert' => E::ts('Invert'), 'apply_on_submit' => E::ts('Submit Implied')], [], null, false);
        $this->add('checkbox', 'original_color', E::ts('Original Background Color'), [], false);
        $this->add('checkbox', 'transparent_background', E::ts('Transparent Background Color'), [], false);
        $this->add('checkbox', 'hide_form_labels', E::ts('Hide text input labels'), [], false);
        $this->add('checkbox', 'add_placeholder', E::ts('Add placeholders'), [], false);
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
    }

    /**
     * This function sets the common fields on the quick form.
     *
     * @param array $fieldNames the fields that are managed by the entity.
     * @param array|null $customSettings the custom settings for the entity.
     *
     * @throws CRM_Core_Exception
     */
    protected function commonPostProcess(array $fieldNames, ?array $customSettings): void
    {
        $submitData = [
            'custom_settings' => $customSettings,
        ];
        foreach ($fieldNames as $key) {
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
            $this->updateCustom($presets);
        } else {
            $this->updateCustom($submitData);
        }
    }

    /*
     * This function gathers the consent custom fields that
     * are present in this form.
     */
    abstract protected function consentActivityCustomFields(): void;

    /*
     * This function has to call the entity.update api
     *
     * @param array $data the new values.
     */
    abstract protected function updateCustom(array $data): void;
}
