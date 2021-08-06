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
        'outro',
        'petition_message',
        'invert_consent_fields',
        'target_number_of_signers',
        'custom_social_box',
        'external_share_url',
        'hide_form_labels',
        'add_placeholder',
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
            throw new CRM_Core_Exception(ts('The selected petition seems to be deleted. Id: %1', [1=>$petitionId]));
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
            "hook_civicrm_appearancemodifierPetitionSettings",
            Civi\Core\Event\GenericHookEvent::create([
                "options" => &$layoutOptions,
            ])
        );
        $this->addRadio('preset_handler',  ts('Presets'), array_merge([''=>ts('Custom')], $layoutOptions['presets']), [], null, false);
        $this->add('select', 'layout_handler', ts('Form Layout'), array_merge([''=>ts('Default')], $layoutOptions)['handlers'], false);
        $this->add('color', 'background_color', ts('Background Color'), [], false);
        $this->add('wysiwyg', 'outro', ts('Outro Text'), [], false);
        $this->add('checkbox', 'invert_consent_fields', ts('Invert Consent Fields'), [], false);
        $this->add('checkbox', 'original_color', ts('Original Background Color'), [], false);
        $this->add('checkbox', 'hide_form_labels', ts('Hide text input labels'), [], false);
        $this->add('checkbox', 'add_placeholder', ts('Add placeholders'), [], false);
        $this->add('textarea', 'petition_message', ts('Petition message'), [], false);
        $this->add('text', 'target_number_of_signers', ts('Target number of signers'), [], false);
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
        $this->setTitle(ts('Customize %1 petition.', [1=>$this->petition['title']]));
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
        }
        $modifiedPetition = AppearancemodifierPetition::update()
            ->setLimit(1)
            ->addWhere('survey_id', '=', $this->petition['id']);
        foreach (self::PETITION_FIELDS as $key) {
            $modifiedPetition = $modifiedPetition->addValue($key, $submitData[$key]);
        }
        $modifiedPetition = $modifiedPetition->execute();
        CRM_Core_Session::setStatus(ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000,]);

        parent::postProcess();
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
