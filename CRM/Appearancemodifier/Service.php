<?php

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
}
