DiamanteDesk Front Bundle
========================

This software is part of DiamanteDesk. Extends its base functionality with front ui available for customers. This will allow customers to submit, monitor status of their tickets through web.


Installation
------------

Add as dependency in composer

```bash
composer require eltrino/diamantedesk-front-bundle:dev-master
```

In addition you will need to run DiamanteDesk internal command to install

```bash
php app/console diamante:front:install
```

This will create additional web root inside your app folder, called "front". You will need to configure web server to point some additional domain/sub domain/folder to this web root.


Development
------------

Source code could be found in `FrontBundle/Resources/front` folder. This folder has such structure

```
./Resources/front
    +-- app
    |   +-- assets
    |   |   +-- js
    |   |   |   +-- app.js
    |   |   |   +-- main.js
    |   +-- index.html
    +-- .bowerrc
    +-- bower.json
    +-- Grungfile.js
```

Application uses bower to manage all assets dependencies, which will be installed in `app/js/vendor`.

To update build with new code changes execute such command

```bash
php app/console diamante:front:update
```

This will update web root `front` but would not fetch new versions of assets. Update web root with new assets should be done through

```bash
php app/console diamante:front:update --with-assets
```