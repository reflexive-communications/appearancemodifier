<?php

use Civi\Appearancemodifier\Service;

require_once 'appearancemodifier.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function appearancemodifier_civicrm_config(&$config): void
{
    _appearancemodifier_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_post().
 * On case of UFGroup create it also creates the Profile entry.
 * On case of Survey create with activity_type 32 (petition signature)
 * it also creates the Petition entry.
 * On case of Event create it also creates the Event entry
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post
 * @throws CRM_Core_Exception
 */
function appearancemodifier_civicrm_post($op, $objectName, $objectId, &$objectRef): void
{
    Service::post($op, $objectName, $objectId, $objectRef);
}

/**
 * Implements hook_civicrm_links().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_links
 */
function appearancemodifier_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values): void
{
    Service::links($op, $links);
}

/**
 * Implements hook_civicrm_alterTemplateFile().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterTemplateFile
 * @throws CRM_Core_Exception
 */
function appearancemodifier_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName): void
{
    Service::alterTemplateFile($tplName, $form);
}

/**
 * Implements hook_civicrm_buildProfile().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildProfile
 * @throws CRM_Core_Exception
 */
function appearancemodifier_civicrm_buildProfile($profileName): void
{
    Service::buildProfile($profileName);
}

/**
 * Implements hook_civicrm_pageRun().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pageRun
 * @throws CRM_Core_Exception
 */
function appearancemodifier_civicrm_pageRun(&$page): void
{
    Service::pageRun($page);
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm
 * @throws CRM_Core_Exception
 */
function appearancemodifier_civicrm_buildForm($formName, &$form): void
{
    Service::buildForm($formName, $form);
}

/**
 * Implements hook_civicrm_alterContent()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterContent
 * @throws CRM_Core_Exception
 */
function appearancemodifier_civicrm_alterContent(&$content, $context, $tplName, &$object): void
{
    Service::alterContent($content, $tplName, $object);
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postProcess
 * @throws CRM_Core_Exception
 */
function appearancemodifier_civicrm_postProcess($formName, $form): void
{
    Service::postProcess($formName, $form);
}
