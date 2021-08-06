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
        'outro',
        'invert_consent_fields',
        'hide_form_labels',
        'add_placeholder',
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
            throw new CRM_Core_Exception(ts('The selected profile seems to be deleted. Id: %1', [1=>$ufGroupId]));
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
        $this->addRadio('preset_handler',  ts('Presets'), array_merge([''=>ts('Custom')], $layoutOptions['presets']), [], null, false);
        $this->add('select', 'layout_handler', ts('Form Layout'), array_merge([''=>ts('Default')], $layoutOptions['handlers']), false);
        $this->add('color', 'background_color', ts('Background Color'), [], false);
        $this->add('wysiwyg', 'outro', ts('Outro Text'), [], false);
        $this->add('checkbox', 'invert_consent_fields', ts('Invert Consent Fields'), [], false);
        $this->add('checkbox', 'original_color', ts('Original Background Color'), [], false);
        $this->add('checkbox', 'add_placeholder', ts('Add placeholders'), [], false);
        $this->add('checkbox', 'hide_form_labels', ts('Hide text input labels'), [], false);
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
        $this->setTitle(ts('Customize %1 profile.', [1=>$this->ufGroup['title']]));
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
        }
        if ($this->_submitValues['preset_handler'] !== '') {
            $this->saveCustomProfile($this->_submitValues['preset_handler']::get());
        } else {
            $this->saveCustomProfile($submitData);
        }
        CRM_Core_Session::setStatus(ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000,]);

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
