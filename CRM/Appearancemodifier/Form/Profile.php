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
        return $this->_defaults;
    }

    /**
     * Build form
     */
    public function buildQuickForm()
    {
        $layoutOptions = [
        ];
        // Fire hook event.
        Civi::dispatcher()->dispatch(
            "hook_civicrm_appearancemodifierProfileLayoutHandlers",
            Civi\Core\Event\GenericHookEvent::create([
                "options" => &$layoutOptions,
            ])
        );
        $this->add('select', 'layout_handler', ts('Form Layout'), array_merge([''=>ts('Default')], $layoutOptions), false);
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
        $modifiedProfile = AppearancemodifierProfile::update()
            ->setLimit(1)
            ->addWhere('uf_group_id', '=', $this->ufGroup['id']);
        foreach (self::PROFILE_FIELDS as $key) {
            $modifiedProfile = $modifiedProfile->addValue($key, $submitData[$key]);
        }
        $modifiedProfile = $modifiedProfile->execute();
        CRM_Core_Session::setStatus(ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000,]);

        parent::postProcess();
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
