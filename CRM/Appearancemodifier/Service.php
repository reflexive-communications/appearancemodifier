<?php

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

    /*
     * This function updates the consent fields of the contact.
     *
     * @param int $contactId
     * @param array $submitValues
     */
    public static function updateConsents(int $contactId, array $submitValues): void
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
     * This function updates the template name on the profile, petition, event
     * pages. The new template includes the original one, but also includes a stylesheet
     * for providing the background color. On petition and profile pages it extends the
     * form with the outro block, if that is set.
     *
     * @param string $tplName
     */
    public static function alterTemplateFile(string &$tplName): void
    {
        if (array_search($tplName, self::TEMPLATE_MAP) !== false) {
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
            $links[] = [
                'name' => 'Customize',
                'url' => 'civicrm/admin/appearancemodifier/profile/customize',
                'qs' => 'pid=%%id%%',
                'title' => 'Customize form with The Appearance Modifier Extension.',
                'class' => 'crm-popup',
            ];
            break;
        case 'petition.dashboard.row':
            $links[] = [
                'name' => 'Customize',
                'url' => 'civicrm/admin/appearancemodifier/petition/customize',
                'qs' => 'pid=%%id%%',
                'title' => 'Customize form with The Appearance Modifier Extension.',
                'class' => 'crm-popup',
            ];
            break;
        case 'event.manage.list':
            $links[] = [
                'name' => 'Customize',
                'url' => 'civicrm/admin/appearancemodifier/event/customize',
                'qs' => 'eid=%%id%%',
                'title' => 'Customize form with The Appearance Modifier Extension.',
                'class' => 'crm-popup',
            ];
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
        } elseif ($page->getVar('_name') == 'CRM_Event_Page_EventInfo') {
            $modifiedEvent = AppearancemodifierEvent::get(false)
                ->addWhere('event_id', '=', $page->getVar('_id'))
                ->execute()
                ->first();
            if ($modifiedEvent['layout_handler'] !== null) {
                $handler = new $modifiedEvent['layout_handler']();
                $handler->setStyleSheets();
            }
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
        } elseif (array_search($formName, $eventFormNames) !== false) {
            $modifiedEvent = AppearancemodifierEvent::get(false)
                ->addWhere('event_id', '=', $form->getVar('_eventId'))
                ->execute()
                ->first();
            if ($modifiedEvent['layout_handler'] !== null) {
                $handler = new $modifiedEvent['layout_handler']();
                $handler->setStyleSheets();
            }
        }
    }
}
