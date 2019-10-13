# Installation

The best way to install Devrun/framework is using Composer:

```sh
composer require devrun/framework
```

## Modules

Module file name is Module.php or .Devrun.php

#### Commands

```sh
php www/index.php devrun:module:list                   # List modules
php www/index.php devrun:module:update                 # Update local database of modules
php www/index.php devrun:module:install <name>         # Install module
php www/index.php devrun:module:uninstall <name>       # Uninstall module
php www/index.php devrun:module:upgrade <name>         # Upgrade module
```
