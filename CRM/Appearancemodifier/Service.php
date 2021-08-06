<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\AppearancemodifierPetition;
use Civi\Api4\AppearancemodifierEvent;
use Civi\Api4\UFGroup;

class CRM_Appearancemodifier_Service
{
    const CONSENT_FIELDS = [
        'do_not_email',
        'do_not_phone',
        'is_opt_out',
    ];
    const TEMPLATE_MAP = [
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

    /*
     * This function updates the template name on the profile, petition, event
     * pages. The new template includes the original one, but also includes a stylesheet
     * for providing the background color. On petition and profile pages it extends the
     * form with the outro block, if that is set.
     *
     * @param string $tplName
     */
    public static function alterTemplateFile(string &$tplName): void
    {
        if (array_key_exists($tplName, self::TEMPLATE_MAP) !== false) {
            $tplName = self::TEMPLATE_MAP[$tplName];
        }
    }

    /*
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
        } elseif ($objectName === 'Survey' && $objectRef->activity_type_id === 32) {
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
     */
    public static function pageRun(&$page): void
    {
        if ($page->getVar('_name') == 'CRM_Campaign_Page_Petition_ThankYou') {
            $modifiedPetition = AppearancemodifierPetition::get(false)
                ->addWhere('survey_id', '=', $page->getVar('petition')['id'])
                ->execute()
                ->first();
            if ($modifiedPetition['layout_handler'] !== null) {
                $handler = new $modifiedPetition['layout_handler']();
                $handler->setStyleSheets();
            }
            Civi::resources()->addStyleFile(E::LONG_NAME, 'assets/css/appearancemodifier.css');
        } elseif ($page->getVar('_name') == 'CRM_Event_Page_EventInfo') {
            $modifiedEvent = AppearancemodifierEvent::get(false)
                ->addWhere('event_id', '=', $page->getVar('_id'))
                ->execute()
                ->first();
            if ($modifiedEvent['layout_handler'] !== null) {
                $handler = new $modifiedEvent['layout_handler']();
                $handler->setStyleSheets();
            }
            Civi::resources()->addStyleFile(E::LONG_NAME, 'assets/css/appearancemodifier.css');
        }
    }

    /**
     * This function extends the profile forms with the stylesheets
     * provided by the layout handler application.
     *
     * @param string $profileName
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
        $modifiedProfile = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $uFGroup['id'])
            ->execute()
            ->first();
        if ($modifiedProfile['layout_handler'] !== null) {
            $handler = new $modifiedProfile['layout_handler']();
            $handler->setStyleSheets();
        }
        Civi::resources()->addStyleFile(E::LONG_NAME, 'assets/css/appearancemodifier.css');
    }

    /**
     * This function extends the petition and event forms with the
     * stylesheets provided by the layout handler application.
     *
     * @param string $formName
     * @param $form
     */
    public static function buildForm(string $formName, &$form): void
    {
        $eventFormNames = [
            'CRM_Event_Form_Registration_Register',
            'CRM_Event_Form_Registration_Confirm',
            'CRM_Event_Form_Registration_ThankYou',
        ];
        if ($formName === 'CRM_Campaign_Form_Petition_Signature') {
            $modifiedPetition = AppearancemodifierPetition::get(false)
                ->addWhere('survey_id', '=', $form->getVar('_surveyId'))
                ->execute()
                ->first();
            if ($modifiedPetition['layout_handler'] !== null) {
                $handler = new $modifiedPetition['layout_handler']();
                $handler->setStyleSheets();
            }
            Civi::resources()->addStyleFile(E::LONG_NAME, 'assets/css/appearancemodifier.css');
        } elseif (array_search($formName, $eventFormNames) !== false) {
            $modifiedEvent = AppearancemodifierEvent::get(false)
                ->addWhere('event_id', '=', $form->getVar('_eventId'))
                ->execute()
                ->first();
            if ($modifiedEvent['layout_handler'] !== null) {
                $handler = new $modifiedEvent['layout_handler']();
                $handler->setStyleSheets();
            }
            Civi::resources()->addStyleFile(E::LONG_NAME, 'assets/css/appearancemodifier.css');
        }
    }

    /**
     * This function handles the consent invertion rule.
     *
     * @param string $formName
     * @param $form
     */
    public static function postProcess(string $formName, $form): void
    {
        $rules = [];
        switch ($formName) {
        case 'CRM_Profile_Form_Edit':
            $rules = AppearancemodifierProfile::get(false)
                ->addWhere('uf_group_id', '=', $form->getVar('_gid'))
                ->execute()
                ->first();
            break;
        case 'CRM_Campaign_Form_Petition_Signature':
            $rules = AppearancemodifierPetition::get(false)
                ->addWhere('survey_id', '=', $form->getVar('_surveyId'))
                ->execute()
                ->first();
            break;
        case 'CRM_Event_Form_Registration_Confirm':
            $rules = AppearancemodifierEvent::get(false)
                ->addWhere('event_id', '=', $form->getVar('_eventId'))
                ->execute()
                ->first();
            break;
        }
        if (array_key_exists('invert_consent_fields', $rules) && $rules['invert_consent_fields'] !== null) {
            self::updateConsents($form->getVar('_id'), $form->getVar('_submitValues'));
        }
    }

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

    /*
     * This function updates the consent fields of the contact.
     *
     * @param int $contactId
     * @param array $submitValues
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

    /*
     * This function handles the layout changes for the profiles.
     * If the layout is specified in the modified profile config,
     * it is applied here.
     *
     * @param int $ufGroupId
     * @param string $content
     */
    private static function alterProfileContent($ufGroupId, &$content): void
    {
        $modifiedProfile = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $ufGroupId)
            ->execute()
            ->first();
        if ($modifiedProfile['layout_handler'] !== null) {
            $handler = new $modifiedProfile['layout_handler']();
            $handler->alterContent($content);
        }
        if ($modifiedProfile['add_placeholder'] !== null) {
            self::setupPlaceholders($content, $modifiedProfile['hide_form_labels']);
        }
    }

    /*
     * This function handles the layout changes for the petitions.
     * If the layout is specified in the modified petition config,
     * it is applied here. If the petition message is given, it is
     * added to the first textarea field on the activity profile.
     * If the custom social block flag is set, it is updated here.
     *
     * @param string $tplName
     * @param string $content
     * @param $object
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
        if ($modifiedPetition['layout_handler'] !== null) {
            $handler = new $modifiedPetition['layout_handler']();
            $handler->alterContent($content);
        }
        // If the petition message is set, add it to the relevant field.
        if ($modifiedPetition['petition_message'] !== null) {
            $doc = phpQuery::newDocument($content);
            if ($doc['.crm-petition-activity-profile']->size() > 0) {
                $doc['.crm-petition-activity-profile textarea:first']->val(new DOMText($modifiedPetition['petition_message']));
            }
            $content = $doc->htmlOuter();
        }
        // Handle the social block.
        if ($modifiedPetition['custom_social_box'] !== null) {
            self::customSocialBlock($content, $modifiedPetition['external_share_url']);
        }
        if ($modifiedPetition['add_placeholder'] !== null) {
            self::setupPlaceholders($content, $modifiedPetition['hide_form_labels']);
        }
    }

    /*
     * This function handles the layout changes for the events.
     * If the layout is specified in the modified event config,
     * it is applied here. If the custom social block flag is set
     * it is updated also.
     *
     * @param string $tplName
     * @param string $content
     * @param $object
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
        if ($modifiedEvent['layout_handler'] !== null) {
            $handler = new $modifiedEvent['layout_handler']();
            $handler->alterContent($content);
        }
        // Handle the social block.
        if ($modifiedEvent['custom_social_box'] !== null) {
            self::customSocialBlock($content, $modifiedEvent['external_share_url']);
        }
        if ($modifiedEvent['add_placeholder'] !== null) {
            self::setupPlaceholders($content, $modifiedEvent['hide_form_labels']);
        }
    }

    /*
     * This function handles the layout changes of the social block.
     *
     * @param string $content
     * @param mixed $extenralUrl
     */
    private static function customSocialBlock(string &$content, $extenralUrl): void
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
                    $twitter = '<div class="social-media-icon"><a href="#" onclick="'.$button->getAttribute('onclick').'" target="_blank" title="'.E::ts('Share on Twitter').'"><div><i aria-hidden="true" class="crm-i fa-twitter"></i></div></a></div>';
                    break;
                case 'crm-fb':
                    $facebook = '<div class="social-media-icon"><a href="#" onclick="'.$button->getAttribute('onclick').'" target="_blank" title="'.E::ts('Share on Facebook').'"><div><i aria-hidden="true" class="crm-i fa-facebook"></i></div></a></div>';
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

    /*
     * This function handles the placeholders. If the hideLabel is set,
     * the hidden-node class is added to the label.
     *
     * @param string $content
     * @param mixed $hideLabels
     */
    private static function setupPlaceholders(string &$content, $hideLabels)
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
}
