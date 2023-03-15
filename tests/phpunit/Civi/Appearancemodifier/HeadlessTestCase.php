<?php

namespace Civi\Appearancemodifier;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\OptionGroup;
use Civi\Api4\OptionValue;
use Civi\Test;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * Testcases for Base Form class with consentactivity extension installed.
 *
 * @group headless
 */
class HeadlessTestCase extends TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
    protected static $index = 0;

    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws \CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB
        Test::headless()
            ->install('rc-base')
            ->install('consentactivity')
            ->installMe(__DIR__)
            ->apply(true);
    }

    /**
     * @return void
     */
    public function setUpHeadless(): void
    {
    }

    protected function createNewCustomField(): array
    {
        // Create custom fields.
        $customGroup = CustomGroup::create(false)
            ->addValue('title', 'Test custom group v'.self::$index)
            ->addValue('extends', 'Contact')
            ->addValue('is_active', 1)
            ->addValue('is_public', 1)
            ->addValue('style', 'Inline')
            ->execute()
            ->first();
        $optionGroup = OptionGroup::create(false)
            ->addValue('title', 'Test option group v'.self::$index)
            ->addValue('name', 'Test option group v'.self::$index)
            ->addValue('data_type', 'String')
            ->addValue('is_public', 1)
            ->execute()
            ->first();
        OptionValue::create(false)
            ->addValue('option_group_id', $optionGroup['id'])
            ->addValue('label', 'Value label v'.self::$index)
            ->addValue('value', '1')
            ->addValue('weight', '1')
            ->execute();
        $customField = CustomField::create(false)
            ->addValue('custom_group_id', $customGroup['id'])
            ->addValue('label', 'Field label v'.self::$index)
            ->addValue('data_type', 'String')
            ->addValue('html_type', 'CheckBox')
            ->addValue('option_group_id', $optionGroup['id'])
            ->addValue('options_per_line', '1')
            ->execute()
            ->first();
        self::$index += 1;

        return $customField;
    }
}
