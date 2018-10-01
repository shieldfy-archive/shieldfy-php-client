# Shieldfy PHP SDK

This is the official PHP SDK for Shieldfy (shieldfy.io) https://shieldfy.io

Shieldfy is a strong application protection platform that helps businesses to secure their applications online.


[![Packagist](https://img.shields.io/packagist/v/shieldfy/shieldfy-php-client.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/shieldfy/shieldfy-php-client)
[![Code Climate](https://img.shields.io/codeclimate/github/shieldfy/shieldfy-php-client.svg)](https://codeclimate.com/github/shieldfy/shieldfy-php-client)
[![StyleCI](https://styleci.io/repos/75610075/shield)](https://styleci.io/repos/75610075)
[![Travis](https://img.shields.io/travis/shieldfy/shieldfy-php-client.svg)](https://travis-ci.org/shieldfy/shieldfy-php-client)



## Installation

You will first need to register on [shieldfy.io](https://shieldfy.io/) to get your APP Key & APP Secret.


Through Composer (the recommended way):

```
composer require shieldfy/shieldfy-php-client
```


## Usage

```php
Shieldfy\Guard::init([
        'app_key'=>'YourAppKey',
        'app_secret'=>'YourAppSecret'
]);
```

## Configurations

For more information about configurations and usage, refer to the official documentation [docs.shieldfy.io](https://docs.shieldfy.io)

## Contributing

Thank you for considering contributing to this project!
Bug reports, feature requests, and pull requests are very welcome.


## Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to security@shieldfy.com.
