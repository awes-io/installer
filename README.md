# Installation

[![Analytics](https://ga-beacon.appspot.com/UA-134431636-1/awes-io/installer)](https://github.com/awes-io/installer)

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

[Awes.IO](https://www.awes.io) utilizes [Composer](https://getcomposer.org/) to manage its dependencies. So, before using [Awes.IO](https://www.awes.io), make sure you have Composer installed on your machine.

First, download the [Awes.IO](https://www.awes.io) installer using Composer:
```bash
composer global require awes-io/installer
```

Make sure to place composer's system-wide vendor bin directory in your `$PATH` so the awes-io executable can be located by your system. This directory exists in different locations based on your operating system; however, some common locations include:

- macOS: `$HOME/.composer/vendor/bin`, command: `export PATH=~/.composer/vendor/bin:$PATH`
- GNU / Linux Distributions: `$HOME/.config/composer/vendor/bin`
- Windows: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`

Once installed, the `awes-io new` command will create a fresh [Awes.IO](https://www.awes.io) installation in the directory you specify. For instance, `awes-io new crm` will create a directory named `crm` containing a fresh [Awes.IO](https://www.awes.io) installation with all of [Awes.IO](https://www.awes.io)'s dependencies already installed:

```bash
awes-io new crm
```

To create demo project use `awes-io demo` command

```bash
awes-io demo crm
```
