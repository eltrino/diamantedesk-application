DiamanteEmbeddedForm
========================

This bundle extends OroEmbeddedFormBundle functionality. It needs for add additional form's type and controller for 
create ticket from third party sites.  

For more information about Embedded Form look at Oro/Bundle/EmbeddedFormBundle/README.md

Installation
------------

Add as dependency in composer

```bash
composer require diamante/embedded-form:dev-master
```

Installation requires additional migration you should run

```bash
php app/console oro:migration:load
```

You should install assets. It should be done through standard Symfony way

```bash
php app/console assets:install
```

Configuration
------------

Diamante Embedded Form url should not be covered by standard Oro Platfrom authentication. You should add additional configuration to filewalls section in `app/etc/security.yml`

```yml
diamante_embedded_form:
    pattern:                        ^/embedded-form/submit-ticket
    provider:                       chain_provider
    anonymous:                      true
```