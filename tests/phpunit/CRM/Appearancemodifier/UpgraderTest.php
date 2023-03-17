<?php

use Civi\Api4\AppearancemodifierEvent;
use Civi\Api4\AppearancemodifierPetition;
use Civi\Api4\AppearancemodifierProfile;
use Civi\Api4\Event;
use Civi\Api4\UFGroup;
use Civi\Appearancemodifier\HeadlessTestCase;
use CRM_Appearancemodifier_ExtensionUtil as E;

/**
 * @group headless
 */
class CRM_Appearancemodifier_UpgraderTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testPostInstall()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        // current number of profiles:
        $profiles = UFGroup::get(false)
            ->addSelect('id')
            ->execute();
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        // current number of petitions ($petition['result']):
        $petitions = civicrm_api3('Survey', 'getcount', [
            'activity_type_id' => 'Petition',
        ]);
        // Event
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        // current number of events:
        $events = Event::get(false)
            ->addSelect('id')
            ->execute();
        // Cleanup the current database.
        $modifiedEvents = AppearancemodifierEvent::get(false)
            ->addSelect('id')
            ->execute();
        foreach ($modifiedEvents as $event) {
            AppearancemodifierEvent::delete(false)
                ->addWhere('id', '=', $event['id'])
                ->execute();
        }
        $modifiedPetitions = AppearancemodifierPetition::get(false)
            ->addSelect('id')
            ->execute();
        foreach ($modifiedPetitions as $petition) {
            AppearancemodifierPetition::delete(false)
                ->addWhere('id', '=', $petition['id'])
                ->execute();
        }
        $modifiedProfiles = AppearancemodifierProfile::get(false)
            ->addSelect('id')
            ->execute();
        foreach ($modifiedProfiles as $profile) {
            AppearancemodifierProfile::delete(false)
                ->addWhere('id', '=', $profile['id'])
                ->execute();
        }
        // call postinstall.
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        $installer->postInstall();
        // check the number of the modified configs. it has to be the same
        // as gathered above.
        $modifiedEvents = AppearancemodifierEvent::get(false)
            ->addSelect('id', 'event_id')
            ->execute();
        $modifiedPetitions = AppearancemodifierPetition::get(false)
            ->addSelect('id', 'survey_id')
            ->execute();
        $modifiedProfiles = AppearancemodifierProfile::get(false)
            ->addSelect('id', 'uf_group_id')
            ->execute();
        self::assertSame(count($modifiedEvents), count($events), 'Invalid number of events.'.var_export($modifiedEvents, true).' - '.var_export($events, true));
        self::assertSame(count($modifiedPetitions), $petitions, 'Invalid number of petitions.'.var_export($modifiedPetitions, true).' - '.var_export($petitions, true));
        self::assertSame(count($modifiedProfiles), count($profiles), 'Invalid number of profiles.'.var_export($modifiedProfiles, true).' - '.var_export($profiles, true));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testUpgrader5300()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile CHANGE additional_note outro text;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition CHANGE additional_note outro text;');
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        self::assertTrue($installer->upgrade_5300());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testUpgrader5301()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile DROP COLUMN font_color;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition DROP COLUMN font_color;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_event DROP COLUMN font_color;');
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        self::assertTrue($installer->upgrade_5301());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testUpgrader5302()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition DROP COLUMN signers_block_position;');
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        self::assertTrue($installer->upgrade_5302());
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testUpgrader5303()
    {
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        // current number of petitions ($petition['result']):
        $petitions = civicrm_api3('Survey', 'getcount', [
            'activity_type_id' => 'Petition',
        ]);
        AppearancemodifierPetition::delete(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->execute();
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        self::assertTrue($installer->upgrade_5303());
        $modifiedPetitions = AppearancemodifierPetition::get(false)
            ->addSelect('id', 'survey_id')
            ->execute();
        self::assertSame(count($modifiedPetitions), $petitions, 'Invalid number of petitions.'.var_export($modifiedPetitions, true).' - '.var_export($petitions, true));
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testUpgrader5304()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile DROP COLUMN consent_field_behaviour;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition DROP COLUMN consent_field_behaviour;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_event DROP COLUMN consent_field_behaviour;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile DROP COLUMN custom_settings;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition DROP COLUMN custom_settings;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_event DROP COLUMN custom_settings;');
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        // Event
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        // Undefined property: CRM_Appearancemodifier_Upgrader::$queue
        $installer->setQueue(
            CRM_Queue_Service::singleton()->create([
                'type' => 'Sql',
                'name' => 'my-own-queue',
                'reset' => true,
            ])
        );
        self::assertTrue($installer->upgrade_5304());
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testUpgrader5304ProfileData()
    {
        // Profile
        $profile = UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        AppearancemodifierProfile::update(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->addValue('invert_consent_fields', true)
            ->addValue('consent_field_behaviour', 'wathever')
            ->execute();
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        self::assertTrue($installer->upgradeExistingProfilesForBehaviour(0));
        $modifiedProfile = AppearancemodifierProfile::get(false)
            ->addWhere('uf_group_id', '=', $profile['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        self::assertSame('invert', $modifiedProfile['consent_field_behaviour']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CiviCRM_API3_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testUpgrader5304PetitionData()
    {
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => 'Some title',
            'activity_type_id' => 'Petition',
        ]);
        AppearancemodifierPetition::update(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->addValue('invert_consent_fields', true)
            ->addValue('consent_field_behaviour', 'wathever')
            ->execute();
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        self::assertTrue($installer->upgradeExistingPetitionsForBehaviour(0));
        $modifiedPetition = AppearancemodifierPetition::get(false)
            ->addWhere('survey_id', '=', $petition['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        self::assertSame('invert', $modifiedPetition['consent_field_behaviour']);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testUpgrader5304EventData()
    {
        // Event
        $event = Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute()
            ->first();
        AppearancemodifierEvent::update(false)
            ->addWhere('event_id', '=', $event['id'])
            ->addValue('invert_consent_fields', true)
            ->addValue('consent_field_behaviour', 'wathever')
            ->execute();
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        self::assertTrue($installer->upgradeExistingEventsForBehaviour(0));
        $modifiedEvent = AppearancemodifierEvent::get(false)
            ->addWhere('event_id', '=', $event['id'])
            ->setLimit(1)
            ->execute()
            ->first();
        self::assertSame('invert', $modifiedEvent['consent_field_behaviour']);
    }
}
