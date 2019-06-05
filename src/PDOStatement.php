<?php

namespace Moln\ReconnectingPDO;

class PDOStatement extends \PDOStatement
{
    protected $pdo;

    /**
     * Protected constructor.
     *
     * @param PDOConnection $pdo
     */
    protected function __construct(PDOConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    private function call($method, $args)
    {
        try {
            return parent::$method(...$args);
        } catch (\PDOException $ex) {
            if (stristr($ex->getMessage(), "server has gone away")) {
                $this->pdo->resetConnection();
            }

            throw $ex;
        }
    }

    public function execute($params = null)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}
