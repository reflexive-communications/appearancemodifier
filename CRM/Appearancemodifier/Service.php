<?php

class CRM_Appearancemodifier_Service
{
    const CONSENT_FIELDS = [
        'do_not_email',
        'do_not_phone',
        'is_opt_out',
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
}
