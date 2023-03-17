<?php

/**
 * AppearancemodifierProfile.create API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 * @throws API_Exception
 * @deprecated
 */
function civicrm_api3_appearancemodifier_profile_create($params): array
{
    return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'AppearancemodifierProfile');
}

/**
 * AppearancemodifierProfile.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 * @throws API_Exception
 * @deprecated
 */
function civicrm_api3_appearancemodifier_profile_delete($params): array
{
    return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AppearancemodifierProfile.get API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 * @throws API_Exception
 * @deprecated
 */
function civicrm_api3_appearancemodifier_profile_get($params): array
{
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, true, 'AppearancemodifierProfile');
}
