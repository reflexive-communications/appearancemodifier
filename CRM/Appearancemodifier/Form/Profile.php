<?php

use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\UFField;
use Civi\Api4\UFGroup;
use Civi\Consentactivity\Config;
use Civi\Consentactivity\Service;
use CRM_Appearancemodifier_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Appearancemodifier_Form_Profile extends CRM_Appearancemodifier_Form_AbstractBase
{
    public const DEFAULT_CUSTOM_SETTINGS = [
        'hide_form_title' => '',
        'send_size_when_embedded' => '',
        'send_size_to_when_embedded' => '*',
        'base_target_is_the_parent' => '',
        'add_check_all_checkbox' => '',
        'check_all_checkbox_label' => '',
    ];

    private const PROFILE_FIELDS = [
        'is_active',
        'layout_handler',
        'background_color',
        'additional_note',
        'consent_field_behaviour',
        'hide_form_labels',
        'add_placeholder',
        'font_color',
    ];

    /**
     * The uf group, for display some stuff about it on the frontend.
     */
    private $ufGroup;

    /**
     * The modified profile
     */
    private $modifiedProfile;

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function preProcess(): void
    {
        // Get the profile id query parameter.
        $ufGroupId = CRM_Utils_Request::retrieve('pid', 'Integer');
        // validate profile id.
        $this->ufGroup = $this->getUfGroup($ufGroupId);
        if ($this->ufGroup === []) {
            throw new CRM_Core_Exception(E::ts('The selected profile seems to be deleted. Id: %1', [1 => $ufGroupId]));
        }
        $this->modifiedProfile = AppearancemodifierProfile::get()
            ->addWhere('uf_group_id', '=', $this->ufGroup['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        parent::preProcess();
    }

    /**
     * @return array
     */
    public function setDefaultValues(): array
    {
        // Set defaults
        foreach (self::PROFILE_FIELDS as $key) {
            $this->_defaults[$key] = $this->modifiedProfile[$key];
        }
        $this->_defaults['pid'] = $this->ufGroup['id'];
        parent::commondDefaultValues($this->modifiedProfile);
        // default for the custom settings.
        parent::customDefaultValues($this->modifiedProfile, self::DEFAULT_CUSTOM_SETTINGS);

        return $this->_defaults;
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function buildQuickForm(): void
    {
        $layoutOptions = [
            'handlers' => [],
            'presets' => [],
        ];
        // Fire hook event.
        Civi::dispatcher()->dispatch(
            'hook_civicrm_appearancemodifierProfileSettings',
            Civi\Core\Event\GenericHookEvent::create([
                'options' => &$layoutOptions,
            ])
        );
        $this->add('hidden', 'pid');
        $this->add('wysiwyg', 'additional_note', E::ts('Additional Note Text'), [], false);
        $this->add('checkbox', 'base_target_is_the_parent', E::ts('Open links in parent frame'), [], false);
        parent::commonBuildQuickForm($layoutOptions);
        $this->setTitle(E::ts('Customize %1 profile.', [1 => $this->ufGroup['title']]));
        parent::buildQuickForm();
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function postProcess(): void
    {
        $customSettings = $this->modifiedProfile['custom_settings'];
        foreach (self::DEFAULT_CUSTOM_SETTINGS as $key => $v) {
            $customSettings[$key] = $this->_submitValues[$key];
        }
        parent::commonPostProcess(self::PROFILE_FIELDS, $customSettings, self::DEFAULT_CUSTOM_SETTINGS);
        CRM_Core_Session::setStatus(E::ts('Data has been updated.'), 'Appearancemodifier', 'success', ['expires' => 5000]);

        parent::postProcess();
    }

    /**
     * This function is a wrapper for AppearancemodifierProfile.update API call.
     *
     * @param array $data the new values.
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    protected function updateCustom(array $data): void
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

    /**
     * This function is a wrapper for UFGroup.Get API call.
     *
     * @param int $id the ufgroup id.
     *
     * @return array the result uf group or empty array.
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
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

    /**
     * This function gathers the consent custom fields that
     * are present in this profile.
     *
     * @throws \CRM_Core_Exception
     */
    protected function consentActivityCustomFields(): void
    {
        // gather the custom fields from the service.
        $consentActivityConfig = new Config('consentactivity');
        $consentActivityConfig->load();
        $config = $consentActivityConfig->get();
        if (array_key_exists('custom-field-map', $config)) {
            $map = $config['custom-field-map'];
            $labels = Service::customCheckboxFields();
            foreach ($map as $rule) {
                // If the current rule field is missing from the profile, continue
                $ufFields = UFField::get()
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
                $this->add(
                    'select',
                    'consentactivity_'.$rule['custom-field-id'],
                    E::ts('Activity for %1', [1 => $labels[$rule['custom-field-id']]]),
                    ['' => E::ts('No Activity')] + CRM_Activity_BAO_Activity::buildOptions('activity_type_id', 'get'),
                    false
                );
                $this->consentFieldNames[] = $rule['custom-field-id'];
            }
        }
    }
}
