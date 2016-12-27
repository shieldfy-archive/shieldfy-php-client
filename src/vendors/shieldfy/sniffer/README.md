# Shieldfy Sniffer

Shieldfy Sniffer is a small composer package to detect the input type , now support 4 types ( integer , string , json , serialize)

[![Packagist](https://img.shields.io/packagist/v/shieldfy/sniffer.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/shieldfy/sniffer)
[![License](https://img.shields.io/packagist/l/shieldfy/sniffer.svg?label=License&style=flat-square)](https://github.com/shieldfy/sniffer/blob/master/LICENSE)
[![Code Climate](https://img.shields.io/codeclimate/github/shieldfy/sniffer.svg)](https://codeclimate.com/github/shieldfy/sniffer)
[![Travis](https://img.shields.io/travis/shieldfy/sniffer.svg)](https://travis-ci.org/shieldfy/sniffer)



## Installation

Through Composer

```
composer require shieldfy/sniffer
```


## Usage & Examples

```php
$type = (new \Shieldfy\Sniffer\Sniffer)->sniff('12.5'); //number

$type = (new \Shieldfy\Sniffer\Sniffer)->sniff('hello world'); //string

$type = (new \Shieldfy\Sniffer\Sniffer)->sniff(json_encode(['hello'=>1,'world'=>'!'])); //json

$type = (new \Shieldfy\Sniffer\Sniffer)->sniff(serialize(['hello'=>1,'world'=>'!'])); //serialize

//you can add more than value as array
$type = (new \Shieldfy\Sniffer\Sniffer)->sniff(['555','abc']);

//test against particular type
$result = (new \Shieldfy\Sniffer\Sniffer)->sniff('123456789','number'); //true

//you can register your own sniffer on the runtime
$type = (new \Shieldfy\Sniffer\Sniffer)->register('hello',function($input){
	if(strstr($input,'hello')) 
		return true;
	return false;
})->sniff('say hello world');

```

## Contributing 

Thank you for considering contributing to this project!
Bug reports, feature requests, and pull requests are very welcome.


## Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to security@shieldfy.com.
