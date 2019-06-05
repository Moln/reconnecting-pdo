<?php

namespace Moln\ReconnectingPDO\Test;

use Moln\ReconnectingPDO\PDOConnection;
use Moln\ReconnectingPDO\PDOStatement;
use PDO;
use PDOException;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;

class PDOConnectionTest extends TestCase
{
    /** @var PDOConnection */
    private $db;

    public function setUp()
    {
        $config = [
            'dsn' => 'mysql:host=127.0.0.1;charset=utf8;',
            'username' => 'root',
            'password' => '',
            'options' => [],
        ];

        //Data center repository
        $db = new PDOConnection(
            $config['dsn'],
            $config['username'],
            $config['password'],
            $config['options']
        );

        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->db = $db;
    }

    public function testQuery()
    {
        $result = $this->db->query('select 1')->fetchColumn();

        $this->assertEquals('1', $result);
    }

    public function testPrepare()
    {
        $sth = $this->db->prepare('select 1');
        $sth->execute();
        $result = $sth->fetchColumn();

        $this->assertInstanceOf(PDOStatement::class, $sth);
        $this->assertEquals('1', $result);
    }


    public function testQueryException()
    {
        Warning::$enabled = false;
        $this->db->query('set session wait_timeout = 1');
        sleep(2);

        set_error_handler(function () {
        });
        $this->testQuery();
        restore_error_handler();
    }

    public function testPrepareException()
    {
        Warning::$enabled = false;
        $this->db->query('set session wait_timeout = 1');

        sleep(2);
        set_error_handler(function () {
        });
        $this->testPrepare();
        restore_error_handler();
    }

    public function testQuote()
    {
        $result = $this->db->quote('"');

        $this->assertEquals('\'\\"\'', $result);
    }
}
