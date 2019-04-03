# Installation

## Server Requirements

* PHP >= 7.1.3
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension
* Ctype PHP Extension
* JSON PHP Extension
* BCMath PHP Extension

## Installing AwesIO

AwesIO utilizes [Composer](https://getcomposer.org/) to manage its dependencies. So, before using AwesIO, make sure you have Composer installed on your machine.

First, download the AwesIO installer using Composer:
```bash
composer global require awes-io/installer
```

Make sure to place composer's system-wide vendor bin directory in your `$PATH` so the awes-io executable can be located by your system. This directory exists in different locations based on your operating system; however, some common locations include:

- macOS: `$HOME/.composer/vendor/bin`
- GNU / Linux Distributions: `$HOME/.config/composer/vendor/bin`
- Windows: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`

Once installed, the `awes-io new` command will create a fresh AwesIO installation in the directory you specify. For instance, `awes-io new blog` will create a directory named `blog` containing a fresh AwesIO installation with all of AwesIO's dependencies already installed:

```bash
awes-io new blog
```
