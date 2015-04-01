DiamanteDesk Front Bundle
========================

This software is part of DiamanteDesk. Extends its base functionality with frontend ui available for customers. This will allow customers to submit, monitor status of their tickets through web.


Requirements
------------

DiamanteDesk Front Bundle requires such software to be installed on target server
- Node.JS
- NPM
- Grunt (installed globally)
- Bower (installed globally)

Installation
------------

Add as dependency in composer

```bash
composer require diamante/front-bundle
```

Composer will also install NPM dependencies. If you're installing bundle not through composer please refer to [NPM Dependencies](#user-content-npm-dependencies "NPM Dependencies")

In addition you will need to run DiamanteDesk internal command to deploy for the first time

```bash
php app/console diamante:front:build --with-assets-dependencies
```

This will create assets in `@DiamanteFrontBundle/Resources/public`. Assets includes compiled less files and dependencies installed via Bower.

Last thing which should be done is assets installation. It should be done through standard way in Symfony

```bash
php app/console assets:install
```

Configuration
------------

Frontend will be accessible through http://app/diamantefront url. This url should not be covered by standard Oro Platfrom authentication. You should add additional configuration to firewalls section in `app/etc/security.yml`

```yml
front_diamante:
    pattern:        ^/diamantefront
    provider:       chain_provider
    anonymous:      true
```

For reset and update password url you should use anonymous user. Add the following rule in the same section as above
```yml
front_diamante_reset_password:
    pattern:        ^/diamantefront/password/*
    provider:       chain_provider
    anonymous:      true
```
            
Development
------------

Source code could be found in `@DiamanteFrontBundle/Resources/front` folder. This folder has such structure

```
@DiamanteFrontBundle
+-- Resources
|    +-- assets
|    |    +-- img
|    |    +-- js
|    |    +-- less
+-- .bowerrc
+-- bower.json
+-- Grungfile.js
```

Application uses Bower to manage all assets dependencies, which will be installed in `assets/js/vendor`.


NPM Dependencies
------------

Composer will install all NPM dependencies during package installation. This is done through `eloquent/composer-npm-bridge`.

All dependencies specified in `@DiamanteFrontBundle/packages.json` and installed in `@DiamanteFrontBundle/node_modules` folder. To update them manually or reinstall, you should use `npm update` or `npm install` inside `@DiamanteFrontBundle` folder.