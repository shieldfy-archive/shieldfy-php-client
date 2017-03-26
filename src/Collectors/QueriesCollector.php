<?php
namespace Shieldfy\Collectors;
use Closure;
use PDO;
use Shieldfy\Config;
use Shieldfy\Collectors\PDO\TraceablePDO;

/**
 * Queries Collector
 */
class QueriesCollector implements Collectable
{

    protected $callback = null;

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function attachDB(PDO $pdo)
    {
        return new TraceablePDO($pdo,array($this,'handler'));
    }

    /**
     * add listener to queries
     * @param  Closure $callback
     */
    public function listen(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Handle query
     * @return [type] [description]
     */
    public function handler($source , $query, $params)
    {
    
        if($this->callback !== null)  call_user_func($this->callback , $source , $query, $params);
    }

    public function getInfo()
    {
        return [];
    }
}
