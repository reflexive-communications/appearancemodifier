<?php

/**
 * Testcase for the AbstractLayout
 * This is a generic test class for the extension (implemented with PHPUnit).
 */
class LayoutImplementation extends CRM_Appearancemodifier_AbstractLayout
{
    public function setStyleSheets(): void
    {
    }
    public function alterContent(&$content): void
    {
    }
}
class CRM_Appearancemodifier_AbstractLayoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The setup() method is executed before the test is executed (optional).
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test for className.
     */
    public function testClassName()
    {
        $impl = new LayoutImplementation('Some_Class_Name');
        self::assertSame('appearancemodifier-', $impl->className());
    }
}
