<?php

use Civi\Appearancemodifier\HeadlessTestCase;

class LayoutImplementation extends CRM_Appearancemodifier_AbstractLayout
{
    public function setStyleSheets(): void
    {
    }

    public function alterContent(&$content): void
    {
    }
}

/**
 * @group headless
 */
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
