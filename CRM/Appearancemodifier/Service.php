<?php

use Civi\Api4\Activity;
use Civi\Api4\AppearancemodifierEvent;
use Civi\Api4\AppearancemodifierPetition;
use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\Contact;
use Civi\Api4\Event;
use Civi\Api4\UFGroup;
use CRM_Appearancemodifier_ExtensionUtil as E;

class CRM_Appearancemodifier_Service
{
    const CONSENT_FIELDS = [
        'do_not_email',
        'do_not_phone',
        'is_opt_out',
    ];

    const TEMPLATE_MAP = [
        'CRM/Profile/Page/View.tpl' => 'CRM/Appearancemodifier/Profile/view.tpl',
        'CRM/Profile/Form/Edit.tpl' => 'CRM/Appearancemodifier/Profile/edit.tpl',
        'CRM/Campaign/Form/Petition/Signature.tpl' => 'CRM/Appearancemodifier/Petition/signature.tpl',
        'CRM/Campaign/Page/Petition/ThankYou.tpl' => 'CRM/Appearancemodifier/Petition/thankyou.tpl',
        'CRM/Event/Page/EventInfo.tpl' => 'CRM/Appearancemodifier/Event/info.tpl',
        'CRM/Event/Form/Registration/Register.tpl' => 'CRM/Appearancemodifier/Event/register.tpl',
        'CRM/Event/Form/Registration/Confirm.tpl' => 'CRM/Appearancemodifier/Event/confirm.tpl',
        'CRM/Event/Form/Registration/ThankYou.tpl' => 'CRM/Appearancemodifier/Event/thankyou.tpl',
    ];

    const PROFILE_TEMPLATES = [
        'CRM/Appearancemodifier/Profile/edit.tpl',
        'CRM/Appearancemodifier/Profile/view.tpl',
    ];

    const PETITION_TEMPLATES = [
        'CRM/Appearancemodifier/Petition/signature.tpl',
        'CRM/Appearancemodifier/Petition/thankyou.tpl',
    ];

    const EVENT_TEMPLATES = [
        'CRM/Appearancemodifier/Event/info.tpl',
        'CRM/Appearancemodifier/Event/register.tpl',
        'CRM/Appearancemodifier/Event/confirm.tpl',
        'CRM/Appearancemodifier/Event/thankyou.tpl',
    ];

    const LINK_PROFILE = [
        'name' => 'Customize',
        'url' => 'civicrm/admin/appearancemodifier/profile/customize',
        'qs' => 'pid=%%id%%',
        'title' => 'Customize form with The Appearance Modifier Extension.',
        'class' => 'crm-popup',
    ];

    const LINK_PETITION = [
        'name' => 'Customize',
        'url' => 'civicrm/admin/appearancemodifier/petition/customize',
        'qs' => 'pid=%%id%%',
        'title' => 'Customize form with The Appearance Modifier Extension.',
        'class' => 'crm-popup',
    ];

    const LINK_EVENT = [
        'name' => 'Customize',
        'url' => 'civicrm/admin/appearancemodifier/event/customize',
        'qs' => 'eid=%%id%%',
        'title' => 'Customize form with The Appearance Modifier Extension.',
        'class' => 'crm-popup',
    ];

    /**
     * This function updates the template name on the profile, petition, event
     * pages. The new template includes the original one, but also includes a stylesheet
     * for providing the background color. On petition and profile pages it extends the
     * form with the additional note block, if that is set.
     *
     * @param string $tplName
     */
    public static function alterTemplateFile(string &$tplName): void
    {
        if (array_key_exists($tplName, self::TEMPLATE_MAP) !== false) {
            $tplName = self::TEMPLATE_MAP[$tplName];
        }
    }

    /**
     * This function provides a link to the customization form on the
     * ufgroup, petition, event lists.
     *
     * @param string $op
     * @param array $links
     */
    public static function links(string $op, array &$links): void
    {
        switch ($op) {
            case 'ufGroup.row.actions':
                $links[] = self::LINK_PROFILE;
                break;
            case 'petition.dashboard.row':
                $links[] = self::LINK_PETITION;
                break;
            case 'event.manage.list':
                $links[] = self::LINK_EVENT;
                break;
        }
    }

    /**
     * On case of UFGroup create it also creates the Profile entry.
     * On case of Survey create with activity_type 32 (petition signature)
     * it also creates the Petition entry.
     * On case of Event create it also creates the Event entry
     *
     * @param string $op
     * @param string $objectName
     * @param $objectId - the unique identifier for the object.
     * @param $objectRef - the reference to the object if available.
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function post(string $op, string $objectName, $objectId, &$objectRef): void
    {
        if ($op !== 'create') {
            return;
        }
        if ($objectName === 'UFGroup') {
            AppearancemodifierProfile::create(false)
                ->addValue('uf_group_id', $objectId)
                ->execute();
        } elseif ($objectName === 'Survey' && intval($objectRef->activity_type_id, 10) === 32) {
            AppearancemodifierPetition::create(false)
                ->addValue('survey_id', $objectId)
                ->execute();
        } elseif ($objectName === 'Event') {
            AppearancemodifierEvent::create(false)
                ->addValue('event_id', $objectId)
                ->execute();
        }
    }

    /**
     * This function extends the pages with the stylesheets
     * provided by the layout handler application.
     *
     * @param $page
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function pageRun(&$page): void
    {
        $modifiedConfig = null;
        if ($page->getVar('_name') == 'CRM_Campaign_Page_Petition_ThankYou') {
            $modifiedConfig = AppearancemodifierPetition::get(false)
                ->addWhere('survey_id', '=', $page->getVar('petition')['id'])
                ->execute()
                ->first();
            if ($modifiedConfig['layout_handler'] !== null) {
                $handler = new $modifiedConfig['layout_handler']('CRM_Campaign_Page_Petition_ThankYou');
                $handler->setStyleSheets();
            }
        } elseif ($page->getVar('_name') == 'CRM_Event_Page_EventInfo') {
            $modifiedConfig = AppearancemodifierEvent::get(false)
                ->addWhere('event_id', '=', $page->getVar('_id'))
                ->execute()
                ->first();
            if ($modifiedConfig['layout_handler'] !== null) {
                $handler = new $modifiedConfig['layout_handler']('CRM_Event_Page_EventInfo');
                $handler->setStyleSheets();
            }
        } elseif ($page->getVar('_name') == 'CRM_Profile_Page_View') {
            $modifiedConfig = AppearancemodifierProfile::get(false)
                ->addWhere('uf_group_id', '=', $page->getVar('_gid'))
                ->execute()
                ->first();
            if ($modifiedConfig['layout_handler'] !== null) {
                $handler = new $modifiedConfig['layout_handler']('CRM_Profile_Page_View');
                $handler->setStyleSheets();
            }
        }
        if ($modifiedConfig !== null) {
            self::setupResourcesBasedOnSettings($modifiedConfig);
        }
    }

    /**
     * This function extends the profile forms with the stylesheets
     * provided by the layout handler application.
     *
     * @param string $profileName
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function buildProfile(string $profileName): void
    {
        // get the profile id form ufgroup api, then use the id for the AppearancemodifierProfile get.
        $uFGroup = UFGroup::get(false)
            ->addSelect('id')
            ->addWhere('name', '=', $profileName)
            ->setLimit(1)
            ->execute()
            ->first();
        // When someone creates a CMS profile, it also calls this with profile name 'unknown'
        // The customized profile obviously does not exists for this profile name.
        if (is_null($uFGroup)) {
            return;
        }
        $modifiedProfile = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $uFGroup['id'])
            ->execute()
            ->first();
        if ($modifiedProfile['layout_handler'] !== null) {
            $handler = new $modifiedProfile['layout_handler']('CRM_Profile_Form_Edit');
            $handler->setStyleSheets();
        }
        self::setupResourcesBasedOnSettings($modifiedProfile);
    }

    /**
     * This function extends the petition and event forms with the
     * stylesheets provided by the layout handler application.
     *
     * @param string $formName
     * @param $form
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function buildForm(string $formName, &$form): void
    {
        $eventFormNames = [
            'CRM_Event_Form_Registration_Register',
            'CRM_Event_Form_Registration_Confirm',
            'CRM_Event_Form_Registration_ThankYou',
        ];
        $modifiedConfig = null;
        if ($formName === 'CRM_Campaign_Form_Petition_Signature') {
            $modifiedConfig = AppearancemodifierPetition::get(false)
                ->addWhere('survey_id', '=', $form->getVar('_surveyId'))
                ->execute()
                ->first();
            if ($modifiedConfig['layout_handler'] !== null) {
                $handler = new $modifiedConfig['layout_handler']($formName);
                $handler->setStyleSheets();
            }
        } elseif (array_search($formName, $eventFormNames) !== false) {
            $modifiedConfig = AppearancemodifierEvent::get(false)
                ->addWhere('event_id', '=', $form->getVar('_eventId'))
                ->execute()
                ->first();
            if ($modifiedConfig['layout_handler'] !== null) {
                $handler = new $modifiedConfig['layout_handler']($formName);
                $handler->setStyleSheets();
            }
        }
        if ($modifiedConfig !== null) {
            self::setupResourcesBasedOnSettings($modifiedConfig);
        }
        Civi::resources()->addScriptFile(E::LONG_NAME, 'js/form-submit-overlay.js');
    }

    /**
     * This function handles the consent invertion rule.
     *
     * @param string $formName
     * @param $form
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function postProcess(string $formName, $form): void
    {
        $rules = [];
        $id = 0;
        $parameters = [];
        switch ($formName) {
            case 'CRM_Profile_Form_Edit':
                $rules = AppearancemodifierProfile::get(false)
                    ->addWhere('uf_group_id', '=', $form->getVar('_gid'))
                    ->execute()
                    ->first();
                $id = $form->getVar('_id');
                $parameters = $form->getVar('_submitValues');
                break;
            case 'CRM_Campaign_Form_Petition_Signature':
                $rules = AppearancemodifierPetition::get(false)
                    ->addWhere('survey_id', '=', $form->getVar('_surveyId'))
                    ->execute()
                    ->first();
                $id = $form->getVar('_contactId');
                $parameters = $form->getVar('_submitValues');
                break;
            case 'CRM_Event_Form_Registration_Register':
                $values = $form->getVar('_values');
                if (!$values['event']['is_confirm_enabled']) {
                    $rules = AppearancemodifierEvent::get(false)
                        ->addWhere('event_id', '=', $form->getVar('_eventId'))
                        ->execute()
                        ->first();
                    $id = $form->getVar('_values')['participant']['contact_id'];
                    $parameters = $form->getVar('_params');
                }
                break;
            case 'CRM_Event_Form_Registration_Confirm':
                $rules = AppearancemodifierEvent::get(false)
                    ->addWhere('event_id', '=', $form->getVar('_eventId'))
                    ->execute()
                    ->first();
                $id = $form->getVar('_values')['participant']['contact_id'];
                $parameters = $form->getVar('_params');
                break;
        }
        if (array_key_exists('consent_field_behaviour', $rules) && $rules['consent_field_behaviour'] !== null) {
            // on case of invert, is used, use the flow that was provided for the invert_consent_fields.
            // on case of apply on submit, the implied consent flow is used.
            // on case of default value the original process is kept.
            if ($rules['consent_field_behaviour'] === 'invert') {
                self::updateConsents($id, $parameters);
            } elseif ($rules['consent_field_behaviour'] === 'apply_on_submit') {
                self::impliedConsentForContact($id);
            }
        }
        self::consentactivityCustomFieldActivities($id, $parameters, $rules);
    }

    /**
     * @param $content
     * @param $tplName
     * @param $object
     *
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public static function alterContent(&$content, $tplName, &$object): void
    {
        if (array_search($tplName, self::PROFILE_TEMPLATES) !== false) {
            self::alterProfileContent($object->getVar('_gid'), $content);

            return;
        }
        if (array_search($tplName, self::PETITION_TEMPLATES) !== false) {
            self::alterPetitionContent($tplName, $content, $object);

            return;
        }
        if (array_search($tplName, self::EVENT_TEMPLATES) !== false) {
            self::alterEventContent($tplName, $content, $object);

            return;
        }
    }

    /**
     * Create the activities based on the consentactivity configuration.
     * Only apply the activity if the extenstion is installed.
     * Also double check that the given fields still appeares in the configurations.
     *
     * @param int $contactId
     * @param array $submitValues
     * @param array $ruleset
     *
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    private static function consentactivityCustomFieldActivities(int $contactId, array $submitValues, array $ruleset): void
    {
        $manager = CRM_Extension_System::singleton()->getManager();
        if ($manager->getStatus('consentactivity') !== CRM_Extension_Manager::STATUS_INSTALLED) {
            return;
        }
        if (!array_key_exists('custom_settings', $ruleset)
            || !is_array($ruleset['custom_settings'])
            || !array_key_exists('consentactivity', $ruleset['custom_settings'])
            || !is_array($ruleset['custom_settings']['consentactivity'])
            || count($ruleset['custom_settings']['consentactivity']) === 0
        ) {
            return;
        }
        // gather the custom fields from the service.
        $consentActivityConfig = new CRM_Consentactivity_Config('consentactivity');
        $consentActivityConfig->load();
        $config = $consentActivityConfig->get();
        if (!array_key_exists('custom-field-map', $config)) {
            return;
        }
        $caConfigMap = $config['custom-field-map'];
        $activityRules = $ruleset['custom_settings']['consentactivity'];
        foreach ($caConfigMap as $caConfigSet) {
            if (array_key_exists($caConfigSet['custom-field-id'], $activityRules)
                && is_array($submitValues[$caConfigSet['custom-field-id']])
                && count($submitValues[$caConfigSet['custom-field-id']])
            ) {
                // allow only the first value in the checkbox array.
                $keys = array_keys($submitValues[$caConfigSet['custom-field-id']]);
                if (empty($submitValues[$caConfigSet['custom-field-id']][$keys[0]])) {
                    continue;
                }
                Activity::create(false)
                    ->addValue('activity_type_id', $activityRules[$caConfigSet['custom-field-id']])
                    ->addValue('source_contact_id', $contactId)
                    ->addValue('target_contact_id', $contactId)
                    ->addValue('status_id:name', 'Completed')
                    ->addValue('skipRecentView', true)
                    ->execute();
            }
        }
    }

    /**
     * This function updates the consent fields of the contact.
     *
     * @param int $contactId
     * @param array $submitValues
     *
     * @throws \CRM_Core_Exception
     */
    private static function updateConsents(int $contactId, array $submitValues): void
    {
        $contactData = [];
        // swap the values of the do_not_email, do_not_phone, is_opt_out fields. '' -> '1', '1' -> ''
        foreach (self::CONSENT_FIELDS as $field) {
            if (array_key_exists($field, $submitValues)) {
                $contactData[$field] = $submitValues[$field] == '' ? '1' : '';
            }
        }
        // update only if the we have something contact related change.
        if (count($contactData) > 0) {
            CRM_RcBase_Api_Update::contact($contactId, $contactData, false);
        }
    }

    /**
     * This function sets the consent fields of the contact to consent is given state.
     * First it gathers the current values of the do_not_phone and is_opt_out privacy
     * fields.
     * It updates the values only if necessary, so civirules could be based of the
     * change event of this value.
     *
     * @param int $contactId
     *
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    private static function impliedConsentForContact(int $contactId): void
    {
        $contact = Contact::get(false)
            ->addSelect('is_opt_out', 'do_not_phone')
            ->addWhere('id', '=', $contactId)
            ->execute()
            ->first();
        $contactData = [];
        foreach (['is_opt_out', 'do_not_phone'] as $field) {
            if ($contact[$field]) {
                $contactData[$field] = '';
            }
        }
        // update only if the we have something contact related change.
        if (count($contactData) > 0) {
            CRM_RcBase_Api_Update::contact($contactId, $contactData, false);
        }
    }

    /**
     * This function handles the layout changes for the profiles.
     * If the layout is specified in the modified profile config,
     * it is applied here.
     *
     * @param int $ufGroupId
     * @param string $content
     *
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    private static function alterProfileContent($ufGroupId, &$content): void
    {
        $modifiedProfile = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $ufGroupId)
            ->execute()
            ->first();
        // add the select all checkbox here and then let the process to do the formatting steps.
        if ($modifiedProfile['custom_settings'] !== null && !empty($modifiedProfile['custom_settings']['add_check_all_checkbox'])) {
            self::addTheSelectAllCheckbox($content, $modifiedProfile['custom_settings']['check_all_checkbox_label']);
        }
        if ($modifiedProfile['add_placeholder'] !== null) {
            self::setupPlaceholders($content, $modifiedProfile['hide_form_labels']);
        }
        self::changeConsentActivityFields($content);
        if ($modifiedProfile['layout_handler'] !== null) {
            $handler = new $modifiedProfile['layout_handler']('CRM_Profile_Form_Edit');
            $handler->alterContent($content);
        }
    }

    /**
     * This function handles the layout changes for the consent fields.
     * The label of the field is replaced with the label of the checkbox.
     *
     * @param string $content
     *
     * @throws \CRM_Core_Exception
     */
    private static function changeConsentActivityFields(&$content): void
    {
        $manager = CRM_Extension_System::singleton()->getManager();
        if ($manager->getStatus('consentactivity') !== CRM_Extension_Manager::STATUS_INSTALLED) {
            return;
        }
        // gather the custom fields from the service.
        $consentActivityConfig = new CRM_Consentactivity_Config('consentactivity');
        $consentActivityConfig->load();
        $config = $consentActivityConfig->get();
        $map = $config['custom-field-map'];
        $doc = phpQuery::newDocument($content);
        foreach ($map as $entry) {
            $id = 'editrow-'.$entry['custom-field-id'];
            $doc['#'.$id]->addClass('consentactivity');
            // Set the checkbox label to the fieldset label and then remove the checkbox label.
            $doc['#'.$id.' div.label label']->replaceWith($doc['#'.$id.' div.content label']);
        }
        $content = $doc->htmlOuter();
    }

    /**
     * This function handles the layout changes for the petitions.
     * If the layout is specified in the modified petition config,
     * it is applied here. If the petition message is given, it is
     * added to the first textarea field on the activity profile.
     * If the custom social block flag is set, it is updated here.
     *
     * @param string $tplName
     * @param string $content
     * @param $object
     *
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    private static function alterPetitionContent($tplName, &$content, &$object): void
    {
        // Get the survey id. If we are on the form, it is in the _surveyId variable,
        // on the thankyou page it is inside the petition array.
        $id = null;
        if ($tplName === self::PETITION_TEMPLATES[0]) {
            $id = $object->getVar('_surveyId');
        } elseif ($tplName === self::PETITION_TEMPLATES[1]) {
            $id = $object->getVar('petition')['id'];
        }
        // if the id is not found, do nothing.
        if (is_null($id)) {
            return;
        }
        // Apply the changes that is provided by the layout extension.
        $modifiedPetition = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $id)
            ->execute()
            ->first();
        // If the petition message is set, add it to the relevant field.
        if ($modifiedPetition['petition_message'] !== null) {
            $doc = phpQuery::newDocument($content);
            if ($doc['.crm-petition-activity-profile']->size() > 0) {
                $doc['.crm-petition-activity-profile textarea:first']->val(new DOMText($modifiedPetition['petition_message']));
                // disable the textarea on case of the config is set.
                if ($modifiedPetition['custom_settings'] !== null && !empty($modifiedPetition['custom_settings']['disable_petition_message_edit'])) {
                    $doc['.crm-petition-activity-profile textarea:first']->attr('disabled', 'disabled');
                }
            }
            $content = $doc->htmlOuter();
        }
        // Handle the social block.
        if ($modifiedPetition['custom_social_box'] !== null) {
            $petitions = civicrm_api3('Survey', 'get', [
                'sequential' => 1,
                'id' => $id,
            ]);
            self::customSocialBlock($content, $modifiedPetition['external_share_url'], $petitions['values'][0]['title']);
        }
        // add the select all checkbox here and then let the process to do the formatting steps.
        if ($modifiedPetition['custom_settings'] !== null && !empty($modifiedPetition['custom_settings']['add_check_all_checkbox'])) {
            self::addTheSelectAllCheckbox($content, $modifiedPetition['custom_settings']['check_all_checkbox_label']);
        }
        if ($modifiedPetition['add_placeholder'] !== null) {
            self::setupPlaceholders($content, $modifiedPetition['hide_form_labels']);
        }
        self::changeConsentActivityFields($content);
        if ($modifiedPetition['layout_handler'] !== null) {
            $handler = new $modifiedPetition['layout_handler']($object->getVar('_name'));
            $handler->alterContent($content);
        }
    }

    /**
     * This function handles the layout changes for the events.
     * If the layout is specified in the modified event config,
     * it is applied here. If the custom social block flag is set
     * it is updated also.
     *
     * @param string $tplName
     * @param string $content
     * @param $object
     *
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    private static function alterEventContent($tplName, &$content, &$object): void
    {
        $id = null;
        if ($tplName === self::EVENT_TEMPLATES[0]) {
            $id = $object->getVar('_id');
        } else {
            $id = $object->getVar('_eventId');
        }
        // if the id is not found, do nothing.
        if (is_null($id)) {
            return;
        }
        $modifiedEvent = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $id)
            ->execute()
            ->first();
        // Handle the social block.
        if ($modifiedEvent['custom_social_box'] !== null) {
            $title = Event::get(false)
                ->addSelect('title')
                ->addWhere('id', '=', $id)
                ->execute()
                ->first()['title'];
            self::customSocialBlock($content, $modifiedEvent['external_share_url'], $title);
        }
        // add the select all checkbox here and then let the process to do the formatting steps.
        if ($modifiedEvent['custom_settings'] !== null && !empty($modifiedEvent['custom_settings']['add_check_all_checkbox'])) {
            self::addTheSelectAllCheckbox($content, $modifiedEvent['custom_settings']['check_all_checkbox_label']);
        }
        if ($modifiedEvent['add_placeholder'] !== null) {
            self::setupPlaceholders($content, $modifiedEvent['hide_form_labels']);
        }
        self::changeConsentActivityFields($content);
        if ($modifiedEvent['layout_handler'] !== null) {
            $handler = new $modifiedEvent['layout_handler']($object->getVar('_name'));
            $handler->alterContent($content);
        }
    }

    /**
     * This function handles the layout changes of the social block.
     *
     * @param string $content
     * @param mixed $externalUrl
     * @param string $eventTitle
     */
    private static function customSocialBlock(string &$content, $externalUrl, $eventTitle = ''): void
    {
        $doc = phpQuery::newDocument($content);
        // Update the social block with custom layout.
        // If not exists, nothing to do with it.
        if ($doc['.crm-socialnetwork']->size() > 0) {
            $twitter = '';
            $facebook = '';
            // Build the new icons based on the data of the original buttons.
            // The onclick event is reused as the click handler of the new social links.
            foreach ($doc['.crm-socialnetwork button'] as $button) {
                switch ($button->getAttribute('id')) {
                    case 'crm-tw':
                        $shareUrl = '';
                        if (!is_null($externalUrl)) {
                            $shareUrl = "window.open('https://twitter.com/intent/tweet?url=".urlencode($externalUrl).'&amp;text='.$eventTitle."', '_blank')";
                        } else {
                            $shareUrl = $button->getAttribute('onclick');
                        }
                        $twitter = sprintf(
                            '<div class="social-media-icon"><a onclick="%s" target="_blank" title="%s"><div><i aria-hidden="true" class="crm-i fa-twitter"></i></div></a></div>',
                            $shareUrl,
                            E::ts('Share on Twitter')
                        );
                        break;
                    case 'crm-fb':
                        if (!is_null($externalUrl)) {
                            $shareUrl = "window.open('https://facebook.com/sharer/sharer.php?u=".urlencode($externalUrl)."', '_blank')";
                        } else {
                            $shareUrl = $button->getAttribute('onclick');
                        }
                        $facebook = sprintf(
                            '<div class="social-media-icon"><a onclick="%s" target="_blank" title="%s"><div><i aria-hidden="true" class="crm-i fa-facebook"></i></div></a></div>',
                            $shareUrl,
                            E::ts('Share on Facebook')
                        );
                        break;
                }
            }
            // Make the update only if the parsing process was successful.
            if ($twitter !== '' || $facebook !== '') {
                // The original block has to be deleted as it is unused.
                $doc['.crm-socialnetwork']->remove();
                // Build the block and append it to the main content.
                $socialTemplate = '<div class="crm-section crm-socialnetwork"><h2>'.E::ts('Please share it').'</h2><div class="appearancemodifier-social-block">'.$facebook.$twitter.'</div></div>';
                $doc['#crm-main-content-wrapper']->append(phpQuery::newDocument($socialTemplate));
            }
        }
        $content = $doc->htmlOuter();
    }

    /**
     * This function handles the placeholders. If the hideLabel is set,
     * the hidden-node class is added to the label.
     *
     * @param string $content
     * @param mixed $hideLabels
     */
    private static function setupPlaceholders(string &$content, $hideLabels): void
    {
        $doc = phpQuery::newDocument($content);
        $domSelectors = [
            '.crm-section.form-item .content input[type="text"]',
            '.crm-section.form-item .content textarea',
        ];
        foreach ($domSelectors as $domSelector) {
            // Add placeholder attribute to the text inputs. The placeholder text has to be the label of the input.
            foreach ($doc[$domSelector] as $textInput) {
                // find the label node, that is the first child of the container node.
                $containerNode = $textInput->parentNode->parentNode;
                $label = $containerNode->firstChild;
                if ($label->nodeType === 3) {
                    $label = $label->nextSibling;
                }
                // The label needs to be cleared. it contains whitespaces, linebraks (the inner trim function handles them)
                // and for the required params we have a * sign after the label, that needs to be removed (rtrim).
                $textInput->setAttribute('placeholder', rtrim(trim($label->nodeValue), " *\n"));
                // if the hidelabel flag is not null, add the hidden node class to the label to make it hidden.
                if (!is_null($hideLabels)) {
                    $label->setAttribute('class', $label->getAttribute('class').' hidden-node');
                }
            }
        }
        $content = $doc->htmlOuter();
    }

    /**
     * This function adds the check all checkbox to the form.
     * When the for does not contain checkboxes, it does nothing,
     * otherwise it adds the checkbox right before the first one.
     *
     * @param string $content
     * @param string $checkboxLabel
     */
    private static function addTheSelectAllCheckbox(string &$content, string $checkboxLabel): void
    {
        $doc = phpQuery::newDocument($content);
        foreach ($doc['input[type="checkbox"]'] as $checkbox) {
            $containerNode = $checkbox;
            $classList = $containerNode->getAttribute('class');
            while (strpos($classList, 'crm-section') === false) {
                $containerNode = $containerNode->parentNode;
                $classList = $containerNode->getAttribute('class');
            }
            $node = new DOMElement('div');
            $containerNode->parentNode->insertBefore($node, $containerNode);
            $node->setAttribute('id', 'check-all-checkbox');
            $checkAllCheckboxTemplate = sprintf(
                '<div class="crm-section form-item"><div class="label"><label for="check-all-checkbox-item">%s</label></div><div class="edit-value content"><input class="crm-form-checkbox" type="checkbox" onclick="checkAllCheckboxClickHandler(this)" id="check-all-checkbox-item"></div><div class="clear"></div></div>',
                $checkboxLabel
            );
            $doc['#check-all-checkbox']->append(phpQuery::newDocument($checkAllCheckboxTemplate));
            break;
        }
        $content = $doc->htmlOuter();
    }

    /**
     * This function sets the resources based on the given configuration.
     *
     * @param array $modifiedConfig
     */
    private static function setupResourcesBasedOnSettings(array $modifiedConfig): void
    {
        Civi::resources()->addStyleFile(E::LONG_NAME, 'assets/css/appearancemodifier.css');
        Civi::resources()->addStyleFile(E::LONG_NAME, 'assets/css/overlay.css');
        if ($modifiedConfig['custom_settings'] !== null) {
            if ($modifiedConfig['custom_settings']['hide_form_title'] === '1') {
                Civi::resources()->addStyleFile(E::LONG_NAME, 'assets/css/hiddentitle.css');
            }
            if ($modifiedConfig['custom_settings']['send_size_when_embedded'] === '1') {
                Civi::resources()->addScriptFile(E::LONG_NAME, 'js/size.js');
            }
            if (isset($modifiedConfig['custom_settings']['base_target_is_the_parent']) && $modifiedConfig['custom_settings']['base_target_is_the_parent'] === '1') {
                Civi::resources()->addScriptFile(E::LONG_NAME, 'js/base-target.js');
            }
            if (isset($modifiedConfig['custom_settings']['add_check_all_checkbox']) && $modifiedConfig['custom_settings']['add_check_all_checkbox'] === '1') {
                Civi::resources()->addScriptFile(E::LONG_NAME, 'js/check-all-checkbox.js');
            }
        }
    }
}
