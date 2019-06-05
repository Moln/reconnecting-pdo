<?php


namespace Moln\ReconnectingPDO\Test;

use Exception;
use PHPUnit\Framework\Assert;

class MockPDO extends \PDO
{
    private static $args;

    private static $exception;

    public static function assertArgs($args)
    {
        self::$args = $args;
    }

    public static function throwException(?Exception $e)
    {
        self::$exception = $e;
    }

    public function __construct()
    {
        Assert::assertEquals(self::$args, func_get_args());
        parent::__construct('sqlite::memory:');
    }

    public function query()
    {
        if (self::$exception) {
            throw self::$exception;
        }

        return parent::query(...func_get_args());
    }
}
