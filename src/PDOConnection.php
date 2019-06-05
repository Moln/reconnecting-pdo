<?php

namespace Moln\ReconnectingPDO;

use PDO;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use function func_get_args;

/**
 * Reconnecting PDO
 *
 */
class PDOConnection extends PDO implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $constructArgs;
    private $conn;
    private $pdoClass = PDO::class;

    public function __construct($dsn, $user = null, $password = null, ?array $options = [], $pdoClass = PDO::class)
    {
        $this->constructArgs = [$dsn, $user, $password, $options];
        $this->setLogger(new NullLogger());

        if (! ($pdoClass == PDO::class || in_array(PDO::class, class_parents($pdoClass)))) {
            throw new \InvalidArgumentException('Invalid pdo implement class.');
        }

        $this->pdoClass = $pdoClass;

        $this->connect();
    }

    private function connect()
    {
        // 使用 parent::__construct 内存溢出, 改用
        $this->conn = new $this->pdoClass(...$this->constructArgs);

        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [PDOStatement::class, [$this]]);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function call($method, $args)
    {
        if (! $this->conn) {
            $this->connect();
        }

        try {
            return $this->conn->$method(...$args);
        } catch (\PDOException $ex) {
            if (stristr($ex->getMessage(), "server has gone away") && $ex->getCode() == 'HY000') {
                $this->resetConnection();
                $this->connect();
                return $this->conn->$method(...$args);
            }

            throw $ex;
        }
    }

    public function exec($statement)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function prepare($prepareString, $driverOptions = [])
    {
        $this->ping();
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = [])
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function quote($input, $type = PDO::PARAM_STR)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function lastInsertId($name = null)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function beginTransaction()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function rollBack()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function commit()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function errorCode()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function errorInfo()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }


    public function getAttribute($attribute)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function setAttribute($attribute, $value)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function inTransaction()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function sqliteCreateFunction($functionName, callable $callback, $numArgs = -1, $flags = 0)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function resetConnection()
    {
        $this->conn = null;
        $this->logger->notice('Reset connection...');
    }

    public function ping()
    {
        return $this->query('select 1')->fetchColumn() == '1';
    }
}
