<?php

namespace Civi\Appearancemodifier;

/**
 * @group headless
 */
class AbstractLayoutTest extends HeadlessTestCase
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
