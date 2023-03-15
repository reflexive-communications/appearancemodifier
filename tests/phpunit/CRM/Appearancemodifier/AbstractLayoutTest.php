<?php

use Civi\Appearancemodifier\HeadlessTestCase;

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

class CRM_Appearancemodifier_AbstractLayoutTest extends HeadlessTestCase
{
    /**
     * Test for className.
     */
    public function testClassName()
    {
        $impl = new LayoutImplementation('Some_Class_Name');
        self::assertSame('appearancemodifier-', $impl->className());
    }
}
