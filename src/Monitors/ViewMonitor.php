<?php
namespace Shieldfy\Monitors;

use Shieldfy\Jury\Judge;

class ViewMonitor extends MonitorBase
{
    use Judge;

    protected $name = 'view';
    protected $template = null;

    protected $vaguePhrases = [
        '<script>','</script>'
    ];

    /**
     * Run the monitor.
     */
    public function run()
    {
        $this->events->listen('view.render', function ($path, $data) {
            if ($this->template != null) {
                return;
            }
            $this->template = [
                'file' => $path,
                'data' => $data
            ];
        });

        $request = $this->collectors['request'];
        $info = $request->getInfo();
        $params = array_merge($info['get'], $info['post']);

        $this->issue('view');

        $infected = [];
        foreach ($params as $key => $value) {
            $result = $this->sentence($value);
            if ($result['score']) {
                $result['value'] = $value;
                $result['key'] = $key;
                $infected[] = $result;
            }
        }
        if (count($infected) > 0) {
            // It's time to listen
            $this->runAnalyzers($infected);
        }
    }

    public function runAnalyzers(array $infected = [])
    {
        $this->infected = $infected;
        ob_start(array($this, 'deepAnalyze'));
    }

    public function deepAnalyze($content)
    {
        $foundGuilty = false;
        $charge = [];
        foreach ($this->infected as $infected):
            if (in_array($infected['value'], $this->vaguePhrases)) {
                continue;
            }
        if (stripos($content, $infected['value']) !== false) {
            $foundGuilty = true;
            $charge = $infected;
            break;
        }
        endforeach;

        if ($foundGuilty) {
            $code = $this->collectors['code']->collectFromText($content, $charge['value']);
            if ($this->template) {
                $code['code']['file'] = $this->template['file'];
                $code['code']['line'] = '-1';
            }

            $severity = $this->parseScore($charge['score']);

            $result = $this->sendToJail($severity, $charge, $code);
            if ($severity == 'high') {
                return $result;
            }
        }

        return $content;
    }
}
