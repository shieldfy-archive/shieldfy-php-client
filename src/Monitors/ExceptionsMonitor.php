<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;

class ExceptionsMonitor extends MonitorBase
{
    use Judge;

    protected $name = 'exceptions';


    /**
     * run the monitor
     */
    public function run()
    {
        $exceptions = $this->collectors['exceptions'];
        $exceptions->listen(function ($exception) {
            $this->deepAnalyze($exception);
        });
    }


    public function deepAnalyze($exception)
    {
        $this->issue('exceptions');
        if (!$this->isInScope($exception)) {
            //echo 'NON';
            return;
        }

        //in scope lets analyze it
        $request = $this->collectors['request'];
        $info = $request->getInfo();
        $params = array_merge($info['get'], $info['post'], $info['cookies']);
        $score = $requestScore = $request->getScore();
        $charge = [];
        foreach ($params as $key => $value) {
            $result = $this->sentence($value, 'REQUEST');
            if ($result['score']) {
                $result['value'] = $value;
                $result['key'] = $key;
                $charge = $result;
                break;
            }
        }


        if ($charge['score'] == 0) {
            return;
        }

        $code = $this->collectors['code']->collectFromFile($exception->getFile(), $exception->getLine());
        
        $this->sendToJail($this->parseScore($charge['score']), $charge, [
            'stack' => $exception->getTrace(),
            'code' => $code
        ]);
    }


    protected function isInScope($exception)
    {
        $message = $exception->getMessage();
        $res = $this->sentence($message, 'EXCEPTION:MSG');
        if ($res['score']) {
            return true;
        }
        $file = $exception->getFile();
        $res = $this->sentence($file, 'EXCEPTION:FILE');
        if ($res['score']) {
            return true;
        }
        return false;
    }
}
