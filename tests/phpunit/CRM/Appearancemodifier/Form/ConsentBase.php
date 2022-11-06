<?php

use CRM_Appearancemodifier_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use Civi\Api4\CustomGroup;
use Civi\Api4\CustomField;
use Civi\Api4\OptionGroup;
use Civi\Api4\OptionValue;

/**
 * Testcases for Base Form class with consentactivity extension installed.
 *
 * @group headless
 */
class CRM_Appearancemodifier_Form_ConsentBase extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
    protected static $index = 0;
    public function setUpHeadless()
    {
        return \Civi\Test::headless()
            ->install('rc-base')
            ->install('consentactivity')
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
            ->install('consentactivity')
            ->installMe(__DIR__)
            ->apply(true);
    }

    public function setUp(): void
    {
        parent::setUp();
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
