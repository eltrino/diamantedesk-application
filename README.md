DiamanteEmbeddedForm
========================

This bundle extends OroEmbeddedFormBundle functionality. It needs for add additional form's type and controller for 
create ticket from third party sites.  

For more information about Embedded Form look at Oro/Bundle/EmbeddedFormBundle/README.md

Configuration
------------

Diamante Embedded Form url should not be covered by standard Oro Platfrom authentication. You should add additional configuration to filewalls section in `app/etc/security.yml`

```yml
diamante_embedded_form:
    pattern:                        ^/embedded-form/submit-ticket
    provider:                       chain_provider
    anonymous:                      true
```