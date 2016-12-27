# Shieldfy Normaizer

This package is useful for the input normalization, before running hardcore IDS/IPS rules. It normalize the inputs to fight against WAF Bypassing techniques using obfuscation or other techniques to hide payloads.

[![Packagist](https://img.shields.io/packagist/v/shieldfy/normalizer.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/shieldfy/normalizer)
[![VersionEye Dependencies](https://img.shields.io/versioneye/d/php/shieldfy:normalizer.svg?label=Dependencies&style=flat-square)](https://www.versioneye.com/php/shieldfy:normalizer/)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/shieldfy/normalizer.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/shieldfy/normalizer/)
[![Code Climate](https://img.shields.io/codeclimate/github/shieldfy/normalizer.svg?label=CodeClimate&style=flat-square)](https://codeclimate.com/github/shieldfy/normalizer)
[![License](https://img.shields.io/packagist/l/shieldfy/normalizer.svg?label=License&style=flat-square)](https://github.com/shieldfy/normalizer/blob/develop/LICENSE)


## Table Of Contents

- [Usage](#usage)
- [Installation](#installation)
- [Changelog](#changelog)
- [Support](#support)
- [Contributing & Protocols](#contributing--protocols)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Credits](#credits)
- [License](#license)


## Usage

Usage is pretty easy and straightforward:

```php
$value = "select/*!from*/information_schema.columns/*!where*/column_name%20/*!like*/char(37,%20112,%2097,%20115,%20115,%2037)";

// Run all normalizers
$result = (new \Shieldfy\Normalizer\Normalizer($value))->runAll();
echo $result;
// select from information_schema.columns where column_name like char(37, 112, 97, 115, 115, 37) %pass%

// Run single normalizer
$result = (new \Shieldfy\Normalizer\Normalizer($value))->run('comments');
```


## Installation

Install the package via composer:
```shell
composer require shieldfy/normalizer
```


## Changelog

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.


## Support

The following support channels are available at your fingertips:

- [Help on Email](mailto:team@shieldfy.com)


## Contributing & Protocols

Thank you for considering contributing to this project! The contribution guide can be found in [CONTRIBUTING.md](CONTRIBUTING.md).

Bug reports, feature requests, and pull requests are very welcome.

- [Versioning](CONTRIBUTING.md#versioning)
- [Pull Requests](CONTRIBUTING.md#pull-requests)
- [Coding Standards](CONTRIBUTING.md#coding-standards)


## Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to [security@shieldfy.com](security@shieldfy.com). All security vulnerabilities will be promptly addressed.


## Credits

This package is based on the original converters written by Mario Heiderich & Christian Matthies the creators of [PHP IDS](https://github.com/PHPIDS/PHPIDS/) project with help from the generous security & opensource community.


## License

This software is released under [The MIT License (MIT)](LICENSE).

(c) 2016 Shieldfy Inc, Some rights reserved.
