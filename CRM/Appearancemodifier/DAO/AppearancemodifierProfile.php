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
 * @property string $uf_group_id 
 * @property string $layout_handler 
 * @property string $background_color 
 * @property string $additional_note 
 * @property bool|string $invert_consent_fields 
 * @property bool|string $add_placeholder 
 * @property bool|string $hide_form_labels 
 * @property string $font_color 
 * @property string $consent_field_behaviour 
 * @property string $custom_settings 
 * @property bool|string $is_active 
 */
class CRM_Appearancemodifier_DAO_AppearancemodifierProfile extends CRM_Appearancemodifier_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'civicrm_appearancemodifier_profile';

}
