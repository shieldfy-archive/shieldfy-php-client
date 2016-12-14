# [Shieldfy PHP Client](https://shieldfy.io/) 


Shieldfy is strong application protection platform that help businesses to secure thier applications online.

## Installation

You will need first to register on [shieldfy.io](https://shieldfy.io/) to get your APP Key & APP Secret.


Through Composer (the recommended way)

```
composer require shieldfy/shieldfy-php-client
```


## Usage

```php
Shieldfy\Guard::init([
        'app_key'=>'YourAppKey',
        'app_secret'=>'YourAppSecret'
])->catchCallbacks();
```

## Configurations

```php
Shieldfy\Guard::init([
        'app_key'=>'YourAppKey',
        'app_secret'=>'YourAppSecret',
        'debug'=>false, //default is false
        'action'=>'block', // what do do when detecting threat . default is block
        'disabledHeaders'=>[] //a list of headers you want to disable.
])->catchCallbacks();
```

for more information about configurations and usage , go to the official documentation [shieldfy.io/docs](https://shieldfy.io/docs)

## Contributing 

Thank you for considering contributing to this project!
Bug reports, feature requests, and pull requests are very welcome.


## Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to security@shieldfy.com.


