# DiamanteDesk EmbeddedForm Bundle #

EmbeddedForm bundle extends OroEmbeddedFormBundle functionality. It is required to add an additional form type and a controller in order to create a ticket from the third party.  

For more information about the Embedded Form, please see **Oro/Bundle/EmbeddedFormBundle/README.md**.

### Requirements ###

DiamanteDesk supports OroCRM version 1.7+.

### Installation ###

Add as dependency in composer:

```bash
composer require diamante/embeddedform-bundle:dev-master
```

Installation requires additional migration:

```bash
php app/console oro:migration:load
```

You should install assets. It should be done in a standard way through Simfony:

```bash
php app/console assets:install
```

### Configuration ###

Diamante Embedded Form URL should not be covered by the standard Oro Platfrom authentication. You should add additional configuration to filewalls section in `app/etc/security.yml`:

```yml
diamante_embedded_form:
    pattern:                        ^/embedded-form/submit-ticket
    provider:                       chain_provider
    anonymous:                      true
```