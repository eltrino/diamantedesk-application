# DiamanteDesk EmbeddedForm Bundle #

EmbeddedForm bundle extends OroEmbeddedFormBundle functionality. To create a ticket from a third party in DiamanteDesk it is required to add an additional form type and a controller.  

For more information about the Embedded Form, please see **Oro/Bundle/EmbeddedFormBundle/README.md**.

### Requirements ###

DiamanteDesk supports OroCRM version 1.8+.

### Installation ###

**Step 1:** Add as dependency in composer:

```bash
composer require diamante/embeddedform-bundle:dev-master
```

**Step 2:** Execute the following command:

```bash
php app/console diamante:embeddedform:schema
```

**Step 3:** Install assets in a usual way through Symfony:

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
### Contributing

We appreciate any effort to make DiamanteDesk functionality better; therefore, we welcome all kinds of contributions in the form of bug reporting, patches submitting, feature requests or documentation enhancement. Please refer to the DiamanteDesk [guidelines for contributing](http://docs.diamantedesk.com/en/latest/developer-guide/contributing.html) if you wish to be a part of the project.
