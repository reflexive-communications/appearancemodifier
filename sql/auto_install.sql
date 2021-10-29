-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
-- 

-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from drop.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_appearancemodifier_profile`;
DROP TABLE IF EXISTS `civicrm_appearancemodifier_petition`;
DROP TABLE IF EXISTS `civicrm_appearancemodifier_event`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_appearancemodifier_event
-- *
-- * This table contains the settings for the modified events.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_appearancemodifier_event` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique AppearancemodifierEvent ID',
     `event_id` int unsigned    COMMENT 'FK to Survey',
     `layout_handler` text    COMMENT 'The alterContent handler function.',
     `background_color` text    COMMENT 'The color code of the background in #ffffff format.',
     `invert_consent_fields` tinyint    COMMENT 'This field triggers the invert behaviour of the consent checkboxes.',
     `custom_social_box` tinyint    COMMENT 'This field triggers the custom social sharing layout.',
     `external_share_url` text    COMMENT 'This link will be shared in the custom social box.',
     `add_placeholder` tinyint    COMMENT 'Set the text input label as placeholder text in the input.',
     `hide_form_labels` tinyint    COMMENT 'Hide the form labels and use only the placeholders.',
     `font_color` text    COMMENT 'The color code of the fonts in #ffffff format.',
     `consent_field_behaviour` text    COMMENT 'This field describes the behaviour of the consent logic.',
     `custom_settings` text    COMMENT 'Serialized data for PHP usage' 
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_civicrm_appearancemodifier_event_event_id FOREIGN KEY (`event_id`) REFERENCES `civicrm_event`(`id`) ON DELETE CASCADE  
)    ;

-- /*******************************************************
-- *
-- * civicrm_appearancemodifier_petition
-- *
-- * This table contains the settings for the modified petitions.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_appearancemodifier_petition` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique AppearancemodifierPetition ID',
     `survey_id` int unsigned    COMMENT 'FK to Survey',
     `layout_handler` text    COMMENT 'The alterContent handler function.',
     `background_color` text    COMMENT 'The color code of the background in #ffffff format.',
     `additional_note` text    COMMENT 'The text that will be displayed after the submit button on the edit form.',
     `petition_message` text    COMMENT 'The text that will be displayed in the petition message input.',
     `invert_consent_fields` tinyint    COMMENT 'This field triggers the invert behaviour of the consent checkboxes.',
     `target_number_of_signers` int    COMMENT 'The target number of the petition signers.',
     `custom_social_box` tinyint    COMMENT 'This field triggers the custom social sharing layout.',
     `external_share_url` text    COMMENT 'This link will be shared in the custom social box.',
     `add_placeholder` tinyint    COMMENT 'Set the text input label as placeholder text in the input.',
     `hide_form_labels` tinyint    COMMENT 'Hide the form labels and use only the placeholders.',
     `font_color` text    COMMENT 'The color code of the fonts in #ffffff format.',
     `signers_block_position` text    COMMENT 'The position where the number of petition signers is displayed.',
     `consent_field_behaviour` text    COMMENT 'This field describes the behaviour of the consent logic.',
     `custom_settings` text    COMMENT 'Serialized data for PHP usage' 
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_civicrm_appearancemodifier_petition_survey_id FOREIGN KEY (`survey_id`) REFERENCES `civicrm_survey`(`id`) ON DELETE CASCADE  
)    ;

-- /*******************************************************
-- *
-- * civicrm_appearancemodifier_profile
-- *
-- * This table contains the settings for the modified profiles.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_appearancemodifier_profile` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique AppearancemodifierProfile ID',
     `uf_group_id` int unsigned NOT NULL   COMMENT 'FK to UFGroup',
     `layout_handler` text    COMMENT 'The alterContent handler function.',
     `background_color` text    COMMENT 'The color code of the background in #ffffff format.',
     `additional_note` text    COMMENT 'The text that will be displayed after the submit button on the edit form.',
     `invert_consent_fields` tinyint    COMMENT 'This field triggers the invert behaviour of the consent checkboxes.',
     `add_placeholder` tinyint    COMMENT 'Set the text input label as placeholder text in the input.',
     `hide_form_labels` tinyint    COMMENT 'Hide the form labels and use only the placeholders.',
     `font_color` text    COMMENT 'The color code of the fonts in #ffffff format.',
     `consent_field_behaviour` text    COMMENT 'This field describes the behaviour of the consent logic.',
     `custom_settings` text    COMMENT 'Serialized data for PHP usage' 
,
        PRIMARY KEY (`id`)
 
 
,          CONSTRAINT FK_civicrm_appearancemodifier_profile_uf_group_id FOREIGN KEY (`uf_group_id`) REFERENCES `civicrm_uf_group`(`id`) ON DELETE CASCADE  
)    ;

 