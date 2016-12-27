<?php
/**
 * Shieldfy Official PHP SDK.
 * Autoloader file
 * @package Shieldfy
 * @link https://shieldfy.io
 * @author Shieldfy Team <team@shieldfy.com>
 * @copyright Shieldfy Inc.
 * @license https://github.com/shieldfy/shieldfy-php-client/blob/master/LICENSE
 */

if (version_compare(PHP_VERSION, '5.4', '<')) {
	throw new Exception("Shieldfy don't support PHP versions before 5.4 , please upgrade , Your current version is ".PHP_VERSION, 1);
}

spl_autoload_register(function($classname){

	$namespace = 'Shieldfy';
	$path = __DIR__.DIRECTORY_SEPARATOR.'src';

	//dirty hack for normalize package
	if(strstr($classname, 'Shieldfy\Normalizer\Normalizer')){
		$path .= DIRECTORY_SEPARATOR.'vendors'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'normalizer'.DIRECTORY_SEPARATOR.'src';
		$namespace = 'Shieldfy\Normalizer\Normalizer';
	}

	//dirty hack for sniffer package
	if(strstr($classname, 'Shieldfy\Sniffer\Sniffer')){
		$path .= DIRECTORY_SEPARATOR.'vendors'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'sniffer'.DIRECTORY_SEPARATOR.'src';
		$namespace = 'Shieldfy\Sniffer\Sniffer';
	}

	//replace the namespace with its folder
	$classPath = str_replace($namespace, $path, $classname);
	$classPath = str_replace('\\', DIRECTORY_SEPARATOR, $classPath);
	$classPath = $classPath . '.php';
	if(is_readable($classPath)){
		require_once $classPath;
	}
},true,true);