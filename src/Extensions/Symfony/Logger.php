<?php

namespace Shieldfy\Extensions\Symfony;

use Shieldfy\Guard;
use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\SQLParserUtils;

use Shieldfy\Extensions\Symfony\ShieldfyBundle;

class Logger implements SQLLogger
{
    /**
     * Last logger quiery
     *
     * @var string
     */
    public $lastQuery = null;

    /**
     * Flag whether echo sql query
     *
     * @var bool
     */
    private $outputQuery = true;

    /**
     * Separator to output after query output
     *
     * @var string
     */
    private $echoSeparator = '<br>';

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if ($params) {
            list($sql, $params, $types) = SQLParserUtils::expandListParameters($sql, $params, $types);
            $query = vsprintf(str_replace('?', "%s", $sql), call_user_func(function() use ($params, $types) {
                $quotedParams = array();
                foreach ($params as $typeIndex => $value) {
                    $quotedParams[] = $this->connection->quote($value, $types[$typeIndex]);
                }
                return $quotedParams;
            }));
        } else {
            $query = $sql;
        }
        $this->lastQuery = $query;
        if ($this->outputQuery) {
            $this->output($query, $params);
        }
    }

    /**
     * Echo the query
     *
     * @param string $query
     */
    protected function output($query, $params)
    {
        $shieldfy = Guard::init();
        $shieldfy->events->trigger('db.query', [$query, $params]);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {

    }

    /**
     * @return boolean
     */
    public function isOutputQuery()
    {
        return $this->outputQuery;
    }

    /**
     * @param boolean $outputQuery
     */
    public function setOutputQuery($outputQuery)
    {
        $this->outputQuery = $outputQuery;
    }

    /**
     * @return string
     */
    public function getEchoSeparator()
    {
        return $this->echoSeparator;
    }

    /**
     * @param string $echoSeparator
     */
    public function setEchoSeparator($echoSeparator)
    {
        $this->echoSeparator = $echoSeparator;
    }
}
