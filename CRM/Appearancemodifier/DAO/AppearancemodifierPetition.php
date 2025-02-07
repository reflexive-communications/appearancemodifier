<?php

/**
 * DAOs provide an OOP-style facade for reading and writing database records.
 *
 * DAOs are a primary source for metadata in older versions of CiviCRM (<5.74)
 * and are required for some subsystems (such as APIv3).
 *
 * This stub provides compatibility. It is not intended to be modified in a
 * substantive way. Property annotations may be added, but are not required.
 * @property string $id 
 * @property string $survey_id 
 * @property string $layout_handler 
 * @property string $background_color 
 * @property string $additional_note 
 * @property string $petition_message 
 * @property bool|string $invert_consent_fields 
 * @property string $target_number_of_signers 
 * @property bool|string $custom_social_box 
 * @property string $external_share_url 
 * @property bool|string $add_placeholder 
 * @property bool|string $hide_form_labels 
 * @property string $font_color 
 * @property string $signers_block_position 
 * @property string $consent_field_behaviour 
 * @property string $custom_settings 
 * @property bool|string $is_active 
 */
class CRM_Appearancemodifier_DAO_AppearancemodifierPetition extends CRM_Appearancemodifier_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'civicrm_appearancemodifier_petition';

}
