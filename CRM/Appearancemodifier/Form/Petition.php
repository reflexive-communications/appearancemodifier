<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Api4\AppearancemodifierPetition;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Appearancemodifier_Form_Petition extends CRM_Appearancemodifier_Form_AbstractBase
{
    public const DEFAULT_CUSTOM_SETTINGS = [
        'hide_form_title' => '',
        'disable_petition_message_edit' => ''
    ];
    private const PETITION_FIELDS = [
        'layout_handler',
        'background_color',
        'additional_note',
        'petition_message',
        'consent_field_behaviour',
        'target_number_of_signers',
        'custom_social_box',
        'external_share_url',
        'hide_form_labels',
        'add_placeholder',
        'font_color',
        'signers_block_position',
    ];
    // The petition, for display some stuff about it on the frontend.
    private $petition;
    // The modified petition
    private $modifiedPetition;

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
        $this->modifiedPetition = AppearancemodifierPetition::get()
            ->addWhere('survey_id', '=', $this->petition['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        parent::preProcess();
    }

    /**
     * Set default values
     *
     * @return array
     */
    public function setDefaultValues()
    {
        // Set defaults
        foreach (self::PETITION_FIELDS as $key) {
            $this->_defaults[$key] = $this->modifiedPetition[$key];
        }
        parent::commondDefaultValues($this->modifiedPetition);
        // default for the custom settings.
        parent::customDefaultValues($this->modifiedPetition, self::DEFAULT_CUSTOM_SETTINGS);
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
        $this->add('textarea', 'petition_message', E::ts('Petition message'), ['rows' => '4', 'cols' => '60'], false);
        $this->add('checkbox', 'disable_petition_message_edit', E::ts('Disable edit'), [], false);
        $this->add('checkbox', 'hide_form_title', E::ts('Hide form title'), [], false);
        $this->add('text', 'target_number_of_signers', E::ts('Target number of signers'), [], false);
        $this->add('checkbox', 'custom_social_box', E::ts('Custom social box'), [], false);
        $this->add('text', 'external_share_url', E::ts('External url to share'), [], false);
        $this->add('wysiwyg', 'additional_note', E::ts('Additional Note Text'), [], false);
        $this->add('select', 'signers_block_position', E::ts('Display Signers Block'), [''=>E::ts('None'), 'top_number' => E::ts('Above only the current number'), 'top_progress' => E::ts('Above progressbar'), 'bottom_number' => E::ts('Below only the current number'), 'bottom_progress' => E::ts('Below progressbar')], false);
        parent::commonBuildQuickForm($layoutOptions);
        $this->setTitle(E::ts('Customize %1 petition.', [1=>$this->petition['title']]));
        parent::buildQuickForm();
    }

    /**
     * Process post data
     */
    public function postProcess()
    {
        $customSettings = $this->modifiedPetition['custom_settings'];
        foreach (self::DEFAULT_CUSTOM_SETTINGS as $key => $v) {
            $customSettings[$key] = $this->_submitValues[$key];
        }
        parent::commonPostProcess(self::PETITION_FIELDS, $customSettings, self::DEFAULT_CUSTOM_SETTINGS);
        CRM_Core_Session::setStatus(E::ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000,]);

        parent::postProcess();
    }

    /**
     * This function is a wrapper for AppearancemodifierPetition.update API call.
     *
     * @param array $data the new values.
     */
    protected function updateCustom(array $data): void
    {
        $modifiedPetition = AppearancemodifierPetition::update()
            ->setLimit(1)
            ->addWhere('survey_id', '=', $this->petition['id']);
        foreach (self::PETITION_FIELDS as $key) {
            $modifiedPetition = $modifiedPetition->addValue($key, $data[$key]);
        }
        if (array_key_exists('custom_settings', $data)) {
            $modifiedPetition = $modifiedPetition->addValue('custom_settings', $data['custom_settings']);
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

    /*
     * This function gathers the consent custom fields that
     * are present in this petition form.
     */
    protected function consentActivityCustomFields(): void
    {
        // gather the custom fields from the service.
        $consentActivityConfig = new CRM_Consentactivity_Config('consentactivity');
        $consentActivityConfig->load();
        $config = $consentActivityConfig->get();
        if (array_key_exists('custom-field-map', $config)) {
            $map = $config['custom-field-map'];
            $labels = CRM_Consentactivity_Service::customCheckboxFields();
            $uFJoins = \Civi\Api4\UFJoin::get()
                ->addSelect('uf_group_id')
                ->addWhere('module', '=', 'CiviCampaign')
                ->addWhere('entity_table', '=', 'civicrm_survey')
                ->addWhere('entity_id', '=', $this->petition['id'])
                ->setLimit(2)
                ->execute();
            $profileIds = [];
            foreach ($uFJoins as $profile) {
                $profileIds[] = $profile['uf_group_id'];
            }
            foreach ($map as $rule) {
                // If the current rule field is missing from the profile, continue
                $ufFields = \Civi\Api4\UFField::get()
                    ->addWhere('uf_group_id', 'IN', $profileIds)
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
