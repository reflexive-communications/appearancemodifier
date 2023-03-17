<?php

use Civi\Appearancemodifier\HeadlessTestCase;

class LayoutImplementation extends CRM_Appearancemodifier_AbstractLayout
{
    /**
     * @return void
     */
    public function setStyleSheets(): void
    {
    }

    /**
     * @param $content
     *
     * @return void
     */
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
     * @return void
     */
    public function testClassName()
    {
        $impl = new LayoutImplementation('Some_Class_Name');
        self::assertSame('appearancemodifier-', $impl->className());
    }
}
