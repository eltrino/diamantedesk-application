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
composer require diamante/front-bundle:dev-master
```

In addition you will need to run DiamanteDesk internal command to deploy for the first time

```bash
php app/console diamante:front:install
```
This will create additional web root inside your app folder `front`. You will need to configure web server to point some additional domain/sub domain/folder to this web root.


Development
------------

Source code could be found in `FrontBundle/Resources/front` folder. This folder has such structure

```
@DiamanteFrontBundle/Resources/front
    +-- assets
    |   +-- js
    |   |   +-- app.js
    |   |   +-- main.js
    |   +-- index.html
    +-- .bowerrc
    +-- bower.json
    +-- Grungfile.js
```

Application uses bower to manage all assets dependencies, which will be installed in `assets/js/vendor`.

To update build with new code changes execute such command

```bash
php app/console diamante:front:update
```

This will update web root `front` but would not fetch new versions of assets. To update web root with new assets use option `--with-assets`

```bash
php app/console diamante:front:update --with-assets
```

NPM Dependencies
------------

Composer will install all NPM dependencies during package installation. This is done through eloquent/composer-npm-bridge.

All dependencies specified in @DiamanteFrontBundle/packages.json and installed in @DiamanteFrontBundle/node_modules folder. To update them manually or reinstall, you should use `npm update` or `npm install` inside @DiamanteFrontBundle folder.
