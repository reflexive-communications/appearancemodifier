<?php

/**
 * AppearancemodifierEvent.create API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appearancemodifier_event_create($params): array
{
    return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'AppearancemodifierEvent');
}

/**
 * AppearancemodifierEvent.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appearancemodifier_event_delete($params): array
{
    return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AppearancemodifierEvent.get API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_appearancemodifier_event_get($params): array
{
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, true, 'AppearancemodifierEvent');
}
