# shieldfy-php-client

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

**Note:** Replace ```Shieldfy``` ```shieldfy``` ```https://shieldfy.io``` ```team@shieldfy.com``` ```shieldfy``` ```shieldfy-php-client``` ```This is the official PHP SDK for Shieldfy (shieldfy.io)``` with their correct values in [README.md](README.md), [CHANGELOG.md](CHANGELOG.md), [CONTRIBUTING.md](CONTRIBUTING.md), [LICENSE.md](LICENSE.md) and [composer.json](composer.json) files, then delete this line. You can run `$ php prefill.php` in the command line to make all replacements at once. Delete the file prefill.php as well.

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what
PSRs you support to avoid any confusion with users and contributors.

## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practises by being named the following.

```
bin/        
config/
src/
tests/
vendor/
```


## Install

Via Composer

``` bash
$ composer require shieldfy/shieldfy-php-client
```

## Usage

``` php
Shieldfy\Guard::init([
        'app_key'=>'YourAppKey',
        'app_secret'=>'YourAppSecret'
])->catchCallbacks();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email team@shieldfy.com instead of using the issue tracker.



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/shieldfy/shieldfy-php-client.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/shieldfy/shieldfy-php-client/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/shieldfy/shieldfy-php-client.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/shieldfy/shieldfy-php-client.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/shieldfy/shieldfy-php-client.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/shieldfy/shieldfy-php-client
[link-travis]: https://travis-ci.org/shieldfy/shieldfy-php-client
[link-scrutinizer]: https://scrutinizer-ci.com/g/shieldfy/shieldfy-php-client/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/shieldfy/shieldfy-php-client
[link-downloads]: https://packagist.org/packages/shieldfy/shieldfy-php-client
