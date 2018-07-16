<?php
namespace Shieldfy\Monitors;

class LibraryMonitor extends MonitorBase
{
    protected $queue = [];

    /**
     * run the monitor
     */
    public function run()
    {
        $basePath = $this->config['paths']['base'];
        $dataPath = $this->config['paths']['data'];
        foreach ($this->config['scanFiles'] as $file) {
            $baseFile = $basePath.'/'.$file;
            $signedFile = $dataPath.'/'.$file.'.sign';
            if (file_exists($baseFile)) {
                //get signature
                $hash = md5_file($baseFile);
                if (!file_exists($signedFile)) {
                    $this->appendFile($file, $baseFile);
                    continue;
                }
                $oldHash = file_get_contents($signedFile);
                if ($hash !== $oldHash) {
                    $this->appendFile($file, $baseFile);
                }
            }
        }
        $this->processFiles();
    }

    private function appendFile($fileName, $filePath)
    {
        $this->queue[$fileName] = str_replace([" ","\t","\n"], '', file_get_contents($filePath));
    }

    private function processFiles()
    {
        if (count($this->queue) == 0) {
            return;
        }

        $result = $this->dispatcher->trigger('security/scan', [
            'host' => $this->collectors['request']->getHost(),
            'files' => $this->queue
        ]);

        if ($result) {
            $this->signTheNewFiles();
        }
    }

    private function signTheNewFiles()
    {
        $basePath = $this->config['paths']['base'];
        $dataPath = $this->config['paths']['data'];
        foreach ($this->queue as $fileName => $fileContents) {
            file_put_contents($dataPath.'/'.$fileName.'.sign', md5_file($basePath.'/'.$fileName));
        }
    }
}
