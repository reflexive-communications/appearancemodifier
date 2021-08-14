<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Api4\AppearancemodifierPetition;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Appearancemodifier_Form_Petition extends CRM_Core_Form
{
    private const PETITION_FIELDS = [
        'layout_handler',
        'background_color',
        'additional_note',
        'petition_message',
        'invert_consent_fields',
        'target_number_of_signers',
        'custom_social_box',
        'external_share_url',
        'hide_form_labels',
        'add_placeholder',
        'font_color',
    ];
    // The petition, for display some stuff about it on the frontend.
    private $petition;

    /**
     * Preprocess form
     *
     * @throws CRM_Core_Exception
     */
    public function preProcess()
    {
        // Get the petition id query parameter.
        $petitionId = CRM_Utils_Request::retrieve('pid', 'Integer');
        // validate profile id.
        $this->petition = $this->getPetition($petitionId);
        if ($this->petition === []) {
            throw new CRM_Core_Exception(E::ts('The selected petition seems to be deleted. Id: %1', [1=>$petitionId]));
        }
    }

    /**
     * Set default values
     *
     * @return array
     */
    public function setDefaultValues()
    {
        $modifiedPetition = AppearancemodifierPetition::get()
            ->addWhere('survey_id', '=', $this->petition['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        // Set defaults
        foreach (self::PETITION_FIELDS as $key) {
            $this->_defaults[$key] = $modifiedPetition[$key];
        }
        if ($modifiedPetition['background_color'] == null) {
            $this->_defaults['original_color'] = 1;
        } elseif ($modifiedPetition['background_color'] === 'transparent') {
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
            "hook_civicrm_appearancemodifierPetitionSettings",
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
        $this->add('checkbox', 'hide_form_labels', E::ts('Hide text input labels'), [], false);
        $this->add('checkbox', 'add_placeholder', E::ts('Add placeholders'), [], false);
        $this->add('textarea', 'petition_message', E::ts('Petition message'), [], false);
        $this->add('text', 'target_number_of_signers', E::ts('Target number of signers'), [], false);
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
        $this->setTitle(E::ts('Customize %1 petition.', [1=>$this->petition['title']]));
        parent::buildQuickForm();
    }

    /**
     * Process post data
     */
    public function postProcess()
    {
        $submitData = [];
        foreach (self::PETITION_FIELDS as $key) {
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
            $this->saveCustomPetition($this->_submitValues['preset_handler']::getPresets());
        } else {
            $this->saveCustomPetition($submitData);
        }
        CRM_Core_Session::setStatus(E::ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000,]);

        parent::postProcess();
    }

    /**
     * This function is a wrapper for AppearancemodifierPetition.update API call.
     *
     * @param array $data the new values.
     */
    private function saveCustomPetition(array $data)
    {
        $modifiedPetition = AppearancemodifierPetition::update()
            ->setLimit(1)
            ->addWhere('survey_id', '=', $this->petition['id']);
        foreach (self::PETITION_FIELDS as $key) {
            $modifiedPetition = $modifiedPetition->addValue($key, $data[$key]);
        }
        $modifiedPetition = $modifiedPetition->execute();
    }

    /*
     * This function is a wrapper for Survey.Get API call.
     *
     * @param int $id the petition id.
     *
     * @return array the result petition or empty array.
     */
    private function getPetition(int $id): array
    {
        $petition = civicrm_api3('Survey', 'get', [
            'sequential' => 1,
            'activity_type_id' => "Petition",
            'id' => $id,
        ]);
        if (count($petition['values']) === 0) {
            return [];
        }
        return $petition['values'][0];
    }
}
