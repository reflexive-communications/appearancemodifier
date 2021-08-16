<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Testcases for Upgrader class.
 *
 * @group headless
 */
class CRM_Appearancemodifier_UpgraderTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
    public function setUpHeadless()
    {
        return \Civi\Test::headless()
            ->install('rc-base')
            ->installMe(__DIR__)
            ->apply();
    }

    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws \CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB and install depended extension
        \Civi\Test::headless()
            ->install('rc-base')
            ->installMe(__DIR__)
            ->apply(true);
    }

    /**
     * Create a clean DB after running tests
     *
     * @throws CRM_Extension_Exception_ParseException
     */
    public static function tearDownAfterClass(): void
    {
        \Civi\Test::headless()
            ->uninstallMe(__DIR__)
            ->uninstall('rc-base')
            ->apply(true);
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /*
     * It tests the postInstall function.
     * Extension install has to be skipped for this tests.
     * It creates a profile a petition and an event form.
     * Calls the postinstall function, then checks the number of
     * entries.
     */
    public function testPostInstall()
    {
        // Profile
        $profile = \Civi\Api4\UFGroup::create(false)
            ->addValue('title', 'Test UFGroup aka Profile')
            ->addValue('is_active', true)
            ->execute()
            ->first();
        // current number of profiles:
        $profiles = \Civi\Api4\UFGroup::get(false)
            ->addSelect('id')
            ->execute();
        // Petition
        $petition = civicrm_api3('Survey', 'create', [
            'sequential' => 1,
            'title' => "Some title",
            'activity_type_id' => "Petition",
        ]);
        // current number of petitions ($petition['result']):
        $petitions = civicrm_api3('Survey', 'getcount', [
            'activity_type_id' => "Petition",
        ]);
        // Event
        $event = \Civi\Api4\Event::create(false)
            ->addValue('title', 'Test event title')
            ->addValue('event_type_id', 4)
            ->addValue('start_date', '2022-01-01')
            ->execute();
        // current number of events:
        $events = \Civi\Api4\Event::get(false)
            ->addSelect('id')
            ->execute();
        // Cleanup the current database.
        $modifiedEvents = \Civi\Api4\AppearancemodifierEvent::get(false)
            ->addSelect('id')
            ->execute();
        foreach ($modifiedEvents as $event) {
            \Civi\Api4\AppearancemodifierEvent::delete(false)
                ->addWhere('id', '=', $event['id'])
                ->execute();
        }
        $modifiedPetitions = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addSelect('id')
            ->execute();
        foreach ($modifiedPetitions as $petition) {
            \Civi\Api4\AppearancemodifierPetition::delete(false)
                ->addWhere('id', '=', $petition['id'])
                ->execute();
        }
        $modifiedProfiles = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addSelect('id')
            ->execute();
        foreach ($modifiedProfiles as $profile) {
            \Civi\Api4\AppearancemodifierProfile::delete(false)
                ->addWhere('id', '=', $profile['id'])
                ->execute();
        }
        // call postinstall.
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        $installer->postInstall();
        // check the number of the modified configs. it has to be the same
        // as gathered above.
        $modifiedEvents = \Civi\Api4\AppearancemodifierEvent::get(false)
            ->addSelect('id', 'event_id')
            ->execute();
        $modifiedPetitions = \Civi\Api4\AppearancemodifierPetition::get(false)
            ->addSelect('id', 'survey_id')
            ->execute();
        $modifiedProfiles = \Civi\Api4\AppearancemodifierProfile::get(false)
            ->addSelect('id', 'uf_group_id')
            ->execute();
        self::assertSame(count($modifiedEvents), count($events), 'Invalid number of events.'.var_export($modifiedEvents, true).' - '.var_export($events, true));
        self::assertSame(count($modifiedPetitions), $petitions, 'Invalid number of petitions.'.var_export($modifiedPetitions, true).' - '.var_export($petitions, true));
        self::assertSame(count($modifiedProfiles), count($profiles), 'Invalid number of profiles.'.var_export($modifiedProfiles, true).' - '.var_export($profiles, true));
    }

    /*
     * It tests the upgrader function.
     * First it alters the tables to the old version, then executes upgrader;
     */
    public function testUpgrader5300()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile CHANGE additional_note outro text;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition CHANGE additional_note outro text;');
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        self::assertTrue($installer->upgrade_5300());
    }
    /*
     * It tests the upgrader function.
     * First it alters the tables to the old version, then executes upgrader;
     */
    public function testUpgrader5301()
    {
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_profile DROP COLUMN font_color;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_petition DROP COLUMN font_color;');
        CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_appearancemodifier_event DROP COLUMN font_color;');
        $installer = new CRM_Appearancemodifier_Upgrader('appearancemodifier', E::path());
        self::assertTrue($installer->upgrade_5301());
    }
}
