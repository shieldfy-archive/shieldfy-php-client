<?php
namespace Shieldfy\Extensions\Symfony\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Shieldfy\Guard;

class ShieldfyExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $shieldfy = Guard::init([
                'app_key'        => $config['app_key'],
                'app_secret'     => $config['app_secret']
        ]);
    }
}
