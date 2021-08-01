<?php

require_once 'appearancemodifier.civix.php';
// phpcs:disable
use CRM_Appearancemodifier_ExtensionUtil as E;

// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function appearancemodifier_civicrm_config(&$config)
{
    _appearancemodifier_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function appearancemodifier_civicrm_xmlMenu(&$files)
{
    _appearancemodifier_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function appearancemodifier_civicrm_install()
{
    _appearancemodifier_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function appearancemodifier_civicrm_postInstall()
{
    _appearancemodifier_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function appearancemodifier_civicrm_uninstall()
{
    _appearancemodifier_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function appearancemodifier_civicrm_enable()
{
    _appearancemodifier_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function appearancemodifier_civicrm_disable()
{
    _appearancemodifier_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function appearancemodifier_civicrm_upgrade($op, CRM_Queue_Queue $queue = null)
{
    return _appearancemodifier_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function appearancemodifier_civicrm_managed(&$entities)
{
    _appearancemodifier_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function appearancemodifier_civicrm_caseTypes(&$caseTypes)
{
    _appearancemodifier_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function appearancemodifier_civicrm_angularModules(&$angularModules)
{
    _appearancemodifier_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function appearancemodifier_civicrm_alterSettingsFolders(&$metaDataFolders = null)
{
    _appearancemodifier_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function appearancemodifier_civicrm_entityTypes(&$entityTypes)
{
    _appearancemodifier_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_themes().
 */
function appearancemodifier_civicrm_themes(&$themes)
{
    _appearancemodifier_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function appearancemodifier_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function appearancemodifier_civicrm_navigationMenu(&$menu) {
//  _appearancemodifier_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _appearancemodifier_civix_navigationMenu($menu);
//}

// The functions below are implemented by me.
/**
 * Implements hook_civicrm_post().
 * On case of UFGroup create it also creates the Profile entry.
 * On case of Survey create with activity_type 32 (petition signature)
 * it also creates the Petition entry.
 * On case of Event create it also creates the Event entry
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post
 */
function appearancemodifier_civicrm_post($op, $objectName, $objectId, &$objectRef)
{
    if ($op !== 'create') {
        return;
    }
    if ($objectName === 'UFGroup') {
        \Civi\Api4\AppearancemodifierProfile::create(false)
            ->addValue('uf_group_id', $objectId)
            ->execute();
    } elseif ($objectName === 'Survey' && $objectRef->activity_type_id === 32) {
        \Civi\Api4\AppearancemodifierPetition::create(false)
            ->addValue('survey_id', $objectId)
            ->execute();
    } elseif ($objectName === 'Event') {
        \Civi\Api4\AppearancemodifierEvent::create(false)
            ->addValue('event_id', $objectId)
            ->execute();
    }
}

/**
 * Implements hook_civicrm_links().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_links
 */
function appearancemodifier_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values)
{
    if ($op === 'ufGroup.row.actions') {
        $links[] = [
            'name' => 'Customize',
            'url' => 'civicrm/admin/appearancemodifier/profile/customize',
            'qs' => 'pid=%%id%%',
            'title' => 'Customize form with The Appearance Modifier Extension.',
            'class' => 'crm-popup',
        ];
    } elseif ($op === 'petition.dashboard.row') {
        $links[] = [
            'name' => 'Customize',
            'url' => 'civicrm/admin/appearancemodifier/petition/customize',
            'qs' => 'pid=%%id%%',
            'title' => 'Customize form with The Appearance Modifier Extension.',
            'class' => 'crm-popup',
        ];
    } elseif ($op === 'event.manage.list') {
        $links[] = [
            'name' => 'Customize',
            'url' => 'civicrm/admin/appearancemodifier/event/customize',
            'qs' => 'eid=%%id%%',
            'title' => 'Customize form with The Appearance Modifier Extension.',
            'class' => 'crm-popup',
        ];
    }
}
/**
 * Implements hook_civicrm_alterTemplateFile().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterTemplateFile
 */
function appearancemodifier_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName)
{
    // On case of profile edit template, replace with a custom one that also includes the original one,
    // but with an additional style block that will contains the color related updates.
    if ($tplName === 'CRM/Profile/Form/Edit.tpl') {
        $tplName = 'CRM/Appearancemodifier/Profile/edit.tpl';
    } else if ($tplName === 'CRM/Campaign/Form/Petition/Signature.tpl') {
        $tplName = 'CRM/Appearancemodifier/Petition/signature.tpl';
    } else if ($tplName === 'CRM/Campaign/Page/Petition/ThankYou.tpl') {
        $tplName = 'CRM/Appearancemodifier/Petition/thankyou.tpl';
    } else if ($tplName === 'CRM/Event/Page/EventInfo.tpl') {
        $tplName = 'CRM/Appearancemodifier/Event/info.tpl';
    } else if ($tplName === 'CRM/Event/Form/Registration/Register.tpl') {
        $tplName = 'CRM/Appearancemodifier/Event/register.tpl';
    } else if ($tplName === 'CRM/Event/Form/Registration/Confirm.tpl') {
        $tplName = 'CRM/Appearancemodifier/Event/confirm.tpl';
    } else if ($tplName === 'CRM/Event/Form/Registration/ThankYou.tpl') {
        $tplName = 'CRM/Appearancemodifier/Event/thankyou.tpl';
    }
}
/**
 * Implements hook_civicrm_buildProfile().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildProfile
 */
function appearancemodifier_civicrm_buildProfile($profileName)
{
    // get the profile id form ufgroup api, then use the id for the AppearancemodifierProfile get.
    $uFGroup = \Civi\Api4\UFGroup::get(false)
        ->addSelect('id')
        ->addWhere('name', '=', $profileName)
        ->setLimit(1)
        ->execute()
        ->first();
    $modifiedProfile = \Civi\Api4\AppearancemodifierProfile::get(false)
        ->addWhere('uf_group_id', '=', $uFGroup['id'])
        ->execute()
        ->first();
    if ($modifiedProfile['layout_handler'] !== null) {
        $handler = new $modifiedProfile['layout_handler']();
        $handler->setStyleSheets();
    }
}
/**
 * Implements hook_civicrm_pageRun().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pageRun
 */
function appearancemodifier_civicrm_pageRun(&$page)
{
    if ($page->getVar('_name') == 'CRM_Campaign_Page_Petition_ThankYou') {
        $modifiedPetition = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $page->getVar('petition')['id'])
            ->execute()
            ->first();
        if ($modifiedPetition['layout_handler'] !== null) {
            $handler = new $modifiedPetition['layout_handler']();
            $handler->setStyleSheets();
        }
    } else if ($page->getVar('_name') == 'CRM_Event_Page_EventInfo') {
        $modifiedEvent = \Civi\Api4\AppearancemodifierEvent::get(false)
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
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm
 */
function appearancemodifier_civicrm_buildForm($formName, &$form)
{
    $eventFormNames = [
        'CRM_Event_Form_Registration_Register',
        'CRM_Event_Form_Registration_Confirm',
        'CRM_Event_Form_Registration_ThankYou',
    ];
    if ($formName === 'CRM_Campaign_Form_Petition_Signature') {
        $modifiedPetition = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $form->getVar('_surveyId'))
            ->execute()
            ->first();
        if ($modifiedPetition['layout_handler'] !== null) {
            $handler = new $modifiedPetition['layout_handler']();
            $handler->setStyleSheets();
        }
    } else if (array_search($formName, $eventFormNames) !== false) {
        $modifiedEvent = \Civi\Api4\AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $form->getVar('_eventId'))
            ->execute()
            ->first();
        if ($modifiedEvent['layout_handler'] !== null) {
            $handler = new $modifiedEvent['layout_handler']();
            $handler->setStyleSheets();
        }
    }
}
/*
 * Implements hook_civicrm_alterContent()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterContent
 *
 */
function appearancemodifier_civicrm_alterContent(&$content, $context, $tplName, &$object)
{
    $petitionTemplates = [
        'CRM/Appearancemodifier/Petition/signature.tpl',
        'CRM/Appearancemodifier/Petition/thankyou.tpl',
    ];
    $eventTemplates = [
        'CRM/Appearancemodifier/Event/info.tpl',
        'CRM/Appearancemodifier/Event/register.tpl',
        'CRM/Appearancemodifier/Event/confirm.tpl',
        'CRM/Appearancemodifier/Event/thankyou.tpl',
    ];
    if ($tplName === 'CRM/Appearancemodifier/Profile/edit.tpl') {
        $modifiedProfile = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $object->getVar('_gid'))
            ->execute()
            ->first();
        if ($modifiedProfile['layout_handler'] !== null) {
            $handler = new $modifiedProfile['layout_handler']();
            $handler->alterContent($content);
        }
    } else if (array_search($tplName, $petitionTemplates) !== false) {
        $id = null;
        if ($tplName === $petitionTemplates[0]) {
            $id = $object->getVar('_surveyId');
        } else if ($tplName === $petitionTemplates[1]) {
            $id = $object->getVar('petition')['id'];
        }
        $modifiedPetition = \Civi\Api4\AppearancemodifierPetition::get(false)
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
        // Handle the social block here.
        if ($modifiedPetition['custom_social_box'] !== null) {
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
                        $faceBook = '<div class="social-media-icon"><a href="#" onclick="'.$button->getAttribute('onclick').'" target="_blank" title="'.E::ts('Share on Facebook').'"><div><i aria-hidden="true" class="crm-i fa-facebook"></i></div></a></div>';
                        break;
                    }
                }
                // Make the update only if the parsing process was successful.
                if ($twitter !== '' || $facebook !== '') {
                    // The original block has to be deleted as it is unused.
                    $doc['.crm-socialnetwork']->remove();
                    // Build the block and append it to the main content.
                    $socialTemplate = '<div class="crm-section crm-socialnetwork"><h2>'.E::ts('Please share it').'</h2><div class="appearancemodifier-social-block">'.$faceBook.$twitter.'</div></div>';
                    $doc['#crm-main-content-wrapper']->append(phpQuery::newDocument($socialTemplate));
                }
            }
            $content = $doc->htmlOuter();
        }
    } else if (array_search($tplName, $eventTemplates) !== false) {
        $id = null;
        if ($tplName === $eventTemplates[0]) {
            $id = $object->getVar('_id');
        } else {
            $id = $object->getVar('_eventId');
        }
        $modifiedEvent = \Civi\Api4\AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $id)
            ->execute()
            ->first();
        if ($modifiedEvent['layout_handler'] !== null) {
            $handler = new $modifiedEvent['layout_handler']();
            $handler->alterContent($content);
        }
        // Handle the social block here.
        if ($modifiedEvent['custom_social_box'] !== null) {
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
                        $faceBook = '<div class="social-media-icon"><a href="#" onclick="'.$button->getAttribute('onclick').'" target="_blank" title="'.E::ts('Share on Facebook').'"><div><i aria-hidden="true" class="crm-i fa-facebook"></i></div></a></div>';
                        break;
                    }
                }
                // Make the update only if the parsing process was successful.
                if ($twitter !== '' || $facebook !== '') {
                    // The original block has to be deleted as it is unused.
                    $doc['.crm-socialnetwork']->remove();
                    // Build the block and append it to the main content.
                    $socialTemplate = '<div class="crm-section crm-socialnetwork"><h2>'.E::ts('Please share it').'</h2><div class="appearancemodifier-social-block">'.$faceBook.$twitter.'</div></div>';
                    $doc['#crm-main-content-wrapper']->append(phpQuery::newDocument($socialTemplate));
                }
            }
            $content = $doc->htmlOuter();
        }
    }
}
/**
 * Implements hook_civicrm_postProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postProcess
 */
function appearancemodifier_civicrm_postProcess($formName, $form)
{
    $rules = [];
    if ($formName === 'CRM_Profile_Form_Edit') {
        $rules = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $form->getVar('_gid'))
            ->execute()
            ->first();
    } else if ($formName === 'CRM_Campaign_Form_Petition_Signature') {
        $rules = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $form->getVar('_surveyId'))
            ->execute()
            ->first();
    } else if ($formName === 'CRM_Event_Form_Registration_Confirm') {
        $rules = \Civi\Api4\AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $form->getVar('_eventId'))
            ->execute()
            ->first();
    }
    if ($rules['invert_consent_fields'] !== null) {
        updateConsents($form->getVar('_id'), $form->getVar('_submitValues'));
    }
}
/*
 * This function updates the consent fields of the contact.
 *
 * @param int $contactId
 * @param array $submitValues
 */
function updateConsents(int $contactId, array $submitValues): void
{
    $contactData = [];
    // swap the values of the do_not_email, do_not_phone, is_opt_out fields. '' -> '1', '1' -> ''
    if (array_key_exists('do_not_email', $submitValues)) {
        $contactData['do_not_email'] = $submitValues['do_not_email'] == '' ? '1' : '';
    }
    if (array_key_exists('do_not_phone', $submitValues)) {
        $contactData['do_not_phone'] = $submitValues['do_not_phone'] == '' ? '1' : '';
    }
    if (array_key_exists('is_opt_out', $submitValues)) {
        $contactData['is_opt_out'] = $submitValues['is_opt_out'] == '' ? '1' : '';
    }
    // update only if the we have something contact related change.
    if (count($contactData) > 0) {
        CRM_RcBase_Api_Update::contact($contactId, $contactData, false);
    }
}
