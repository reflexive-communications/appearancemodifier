<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Testcases for Service class.
 *
 * @group headless
 */
class CRM_Appearancemodifier_ServiceTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
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
     * It tests the alterTemplateFile function.
     */
    public function testAlterTemplateFile()
    {
        // mapped files
        foreach (CRM_Appearancemodifier_Service::TEMPLATE_MAP as $template => $value) {
            self::assertSame($value, CRM_Appearancemodifier_Service::alterTemplateFile($template));
        }
        // not mapped file
        $template = 'not/mapped/template/file.tpl';
        self::assertSame($template, CRM_Appearancemodifier_Service::alterTemplateFile($template));
    }

    /*
     * It tests the links function.
     */
    public function testLinks()
    {
        $ops = [
            'ufGroup.row.actions' => CRM_Appearancemodifier_Service::LINK_PROFILE,
            'petition.dashboard.row' => CRM_Appearancemodifier_Service::LINK_PETITION,
            'event.manage.list' => CRM_Appearancemodifier_Service::LINK_EVENT,
        ];
        foreach ($ops as $op => $v) {
            $links = [];
            CRM_Appearancemodifier_Service::links($op, $links);
            self::assertCount(1, $links);
            self::assertSame($v, $links[0]);
        }
        $links = [];
        $op = 'something.not.handled';
        CRM_Appearancemodifier_Service::links($op, $links);
        self::assertCount(0, $links);
    }

    /*
     * It tests the pageRun function.
     */
    public function testPageRun()
    {
        self::markTestIncomplete('This test has not been implemented yet.');
    }

    /*
     * It tests the buildProfile function.
     */
    public function testBuildProfile()
    {
        self::markTestIncomplete('This test has not been implemented yet.');
    }

    /*
     * It tests the buildForm function.
     */
    public function testBuildForm()
    {
        self::markTestIncomplete('This test has not been implemented yet.');
    }

    /*
     * It tests the postProcess function.
     */
    public function testPostProcess()
    {
        self::markTestIncomplete('This test has not been implemented yet.');
    }

    /*
     * It tests the alterContent function.
     */
    public function testAlterContent()
    {
        self::markTestIncomplete('This test has not been implemented yet.');
    }
}
