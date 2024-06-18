<?php

use Civi\Consentactivity\Service;
use CRM_Appearancemodifier_ExtensionUtil as E;

/**
 * Abstract form controller class
 * It handles the common steps across the implementations.
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
abstract class CRM_Appearancemodifier_Form_AbstractBase extends CRM_Core_Form
{
    /**
     * consentactivity related configurations
     */
    protected $consentFieldNames;

    /**
     * @return void
     */
    public function preProcess(): void
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
            $this->_defaults['consent_field_behaviour'] = $customFormData['invert_consent_fields'] == null ? 'default' : 'invert';
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
     * This function sets the default values for the fields that are present
     * in the customization.
     *
     * @param array $customFormData the entity data.
     * @param array $variables the custom data.
     */
    protected function customDefaultValues(array $customFormData, array $variables): void
    {
        foreach ($variables as $key => $defaultValue) {
            $value = ($customFormData['custom_settings'] !== null && array_key_exists($key, $customFormData['custom_settings'])) ? $customFormData['custom_settings'][$key] : $defaultValue;
            $this->_defaults[$key] = $value;
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
        $this->addYesNo('is_active', E::ts('Is Active'), false, true);
        $this->addRadio('preset_handler', E::ts('Presets'), array_merge(['' => E::ts('Custom')], $customOptions['presets']));
        $this->add('select', 'layout_handler', E::ts('Form Layout'), array_merge(['' => E::ts('Default')], $customOptions['handlers']));
        $this->add('color', 'background_color', E::ts('Background Color'), []);
        $this->addRadio(
            'consent_field_behaviour',
            E::ts('Manage Consent Behaviour'),
            [
                'default' => E::ts('Default'),
                'invert' => E::ts('Invert'),
                'apply_on_submit' => E::ts('Submit Implied'),
            ]
        );
        $this->add('checkbox', 'original_color', E::ts('Original Background Color'), []);
        $this->add('checkbox', 'transparent_background', E::ts('Transparent Background Color'), []);
        $this->add('checkbox', 'hide_form_labels', E::ts('Hide text input labels'), []);
        $this->add('checkbox', 'add_placeholder', E::ts('Add placeholders'), []);
        $this->add('color', 'font_color', E::ts('Font Color'), []);
        $this->add('checkbox', 'original_font_color', E::ts('Original Font Color'), []);
        $this->add('checkbox', 'hide_form_title', E::ts('Hide form title'), []);
        $this->add('checkbox', 'send_size_when_embedded', E::ts('Send size to parent frame'), []);
        $this->add('text', 'send_size_to_when_embedded', E::ts('Parent frame'), [], true);
        $this->add('checkbox', 'add_check_all_checkbox', E::ts('Add check all checkbox'), []);
        $this->add('text', 'check_all_checkbox_label', E::ts('Checkbox label'), []);
        // If the consentactivity extension is installed, the custom consent field -> activity mapping has to be provided
        // defaults for the consentactivity extension related config.
        if (count($this->consentFieldNames) > 0) {
            $consentActivityFieldNames = [];
            $labels = Service::customCheckboxFields();
            foreach ($this->consentFieldNames as $field) {
                $this->add(
                    'select',
                    'consentactivity_'.$field,
                    E::ts('Activity for %1', [1 => $labels[$field]]),
                    ['' => E::ts('No Activity')] + CRM_Activity_BAO_Activity::buildOptions('activity_type_id', 'get'),
                    false
                );
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
     * @param array|null $currenCustomSettings the custom settings for the entity.
     * @param array $defaultCustomSettings the default custom settings for the entity for presets.
     *
     * @throws CRM_Core_Exception
     */
    protected function commonPostProcess(array $fieldNames, ?array $currenCustomSettings, array $defaultCustomSettings = []): void
    {
        $submitData = [
            'custom_settings' => $currenCustomSettings,
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
                $presets['custom_settings'] = $defaultCustomSettings;
            }
            $this->updateCustom($presets);
        } else {
            $this->updateCustom($submitData);
        }
    }

    /**
     * This function gathers the consent custom fields that
     * are present in this form.
     */
    abstract protected function consentActivityCustomFields(): void;

    /**
     * This function has to call the entity.update api
     *
     * @param array $data the new values.
     */
    abstract protected function updateCustom(array $data): void;
}
