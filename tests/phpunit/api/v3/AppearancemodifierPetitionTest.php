<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * AppearancemodifierPetition API Test Case
 * @group headless
 */
class api_v3_AppearancemodifierPetitionTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
    use \Civi\Test\Api3TestTrait;

    /**
     * Set up for headless tests.
     *
     * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
     *
     * See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
     */
    public function setUpHeadless()
    {
        return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
    }

    /**
     * The setup() method is executed before the test is executed (optional).
     */
    public function setUp(): void
    {
        $table = CRM_Core_DAO_AllCoreTables::getTableForEntityName('AppearancemodifierPetition');
        $this->assertTrue($table && CRM_Core_DAO::checkTableExists($table), 'There was a problem with extension installation. Table for ' . 'AppearancemodifierPetition' . ' not found.');
        parent::setUp();
    }

    /**
     * The tearDown() method is executed after the test was executed (optional)
     * This can be used for cleanup.
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
