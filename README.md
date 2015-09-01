# DiamanteDesk Front Bundle #

DiamanteDeskFrontBundle is a part of DiamanteDesk system. This software extends its base functionality with frontend UI available for customers, allowing them to submit and monitor status of their tickets through web.

### Installation ###

Add as dependency in composer:

```bash
composer require diamante/front-bundle
```
Install assets via Symfony using the following command:

```bash
php app/console assets:install
```

### Configuration ###

Frontend is accessible at [this URL](http://app/portal). This URL should not be covered by standard Oro Platfrom authentication. You should add additional configuration to firewalls section in `app/etc/security.yml`:

```yml
front_diamante:
    pattern:        ^/portal
    provider:       chain_provider
    anonymous:      true
```

In order to reset and update password url you should use anonymous user. Add the following rule in the same section as above:

```yml
front_diamante_reset_password:
    pattern:        ^/portal/password/*
    provider:       chain_provider
    anonymous:      true
```
            
### Development ###

###### Requirements for Development ###

DiamanteDesk Front Bundle development requires such software to be installed:

- Node.JS
- NPM
- Grunt (installed globally)
- Bower (installed globally)

The source code can be found in the `@DiamanteFrontBundle/Resources/front` folder. This folder has the following structure:

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

Application uses **Bower** to manage all asset dependencies, which will be installed in `assets/js/vendor`.

After the source code is changed, run [Grunt](http://gruntjs.com/) or execute the following command:

```
php app/console diamante:front:build
```
### NPM Dependencies ###

All dependencies specified in `@DiamanteFrontBundle/packages.json` and installed in `@DiamanteFrontBundle/node_modules` folder. To update them manually or reinstall, you should use `npm update` or `npm install` in the `@DiamanteFrontBundle` folder.

## Contributing

We appreciate any effort to make DiamanteDesk functionality better; therefore, we welcome all kinds of contributions in the form of bug reporting, patches submition, feature requests or documentation enhancement. Please refer to the DiamanteDesk [guidelines for contributing](http://docs.diamantedesk.com/en/latest/developer-guide/contributing.html) if you wish to be a part of the project.
