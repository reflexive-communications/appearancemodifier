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
        'invert_consent_fields',
        'hide_form_labels',
        'add_placeholder',
        'font_color',
    ];
    // The uf group, for display some stuff about it on the frontend.
    private $ufGroup;

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
    }

    /**
     * Set default values
     *
     * @return array
     */
    public function setDefaultValues()
    {
        $modifiedProfile = AppearancemodifierProfile::get()
            ->addWhere('uf_group_id', '=', $this->ufGroup['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        // Set defaults
        foreach (self::PROFILE_FIELDS as $key) {
            $this->_defaults[$key] = $modifiedProfile[$key];
        }
        if ($modifiedProfile['background_color'] == null) {
            $this->_defaults['original_color'] = 1;
        } elseif ($modifiedProfile['background_color'] === 'transparent') {
            $this->_defaults['transparent_background'] = 1;
            $this->_defaults['background_color'] = null;
        }
        if ($modifiedEvent['font_color'] == null) {
            $this->_defaults['original_font_color'] = 1;
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
            "hook_civicrm_appearancemodifierProfileSettings",
            Civi\Core\Event\GenericHookEvent::create([
                "options" => &$layoutOptions,
            ])
        );
        $this->addRadio('preset_handler', E::ts('Presets'), array_merge([''=>E::ts('Custom')], $layoutOptions['presets']), [], null, false);
        $this->add('select', 'layout_handler', E::ts('Form Layout'), array_merge([''=>E::ts('Default')], $layoutOptions['handlers']), false);
        $this->add('color', 'background_color', E::ts('Background Color'), [], false);
        $this->add('wysiwyg', 'additional_note', E::ts('Additional Note Text'), [], false);
        $this->add('checkbox', 'invert_consent_fields', E::ts('Invert Consent Fields'), [], false);
        $this->add('checkbox', 'original_color', E::ts('Original Background Color'), [], false);
        $this->add('checkbox', 'transparent_background', E::ts('Transparent Background Color'), [], false);
        $this->add('checkbox', 'add_placeholder', E::ts('Add placeholders'), [], false);
        $this->add('checkbox', 'hide_form_labels', E::ts('Hide text input labels'), [], false);
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
        $this->setTitle(E::ts('Customize %1 profile.', [1=>$this->ufGroup['title']]));
        parent::buildQuickForm();
    }

    /**
     * Process post data
     */
    public function postProcess()
    {
        $submitData = [];
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
        if ($this->_submitValues['preset_handler'] !== '') {
            $this->saveCustomProfile($this->_submitValues['preset_handler']::getPresets());
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
}
