# DiamanteDesk Bundle #

DiamanteDesk software extends basic OroCRM functionality. Its main purpose is to integrate customer support system into the Client's CRM system.

Using this comprehensive help desk, it is now possible to create support tickets within the CRM and associate them with any customer from the system. In DiamanteDesk, tickets can be assigned to specific users and grouped into Branches. This option allows grouping tickets related to a certain customer.

Currently, DiamanteDesk software is in alpha version. Eltrino team is making efforts to improve and increase the amount of features available to our clients.

### Requirements ###

DiamanteDesk supports OroCRM version 1.8+.

### Installation ###

**Step 1:** Add as dependency in composer:

```bash
composer require diamante/desk-bundle:dev-master
```
**Step 2:** To properly install DiamanteDesk bundle, create the following folders:

* app/attachments
* web/uploads/branch/logo

**Step 3:** Run DiamanteDesk internal command:

```bash
php app/console diamante:desk:install
```

To update the software that has already been installed, execute the followong commands:

```bash
php app/console diamante:desk:schema
php app/console oro:migration:load
php app/console diamante:desk:data
php app/console oro:navigation:init
php app/console oro:entity-config:update
```

After *install* or *update* commands are issued, assets shall be installed in a usual way through Symfony:

```bash
php app/console assets:install
```

### Configuration ###

To download the attachment, add additional configuration to firewalls section in `app/etc/security.yml`

```yml
diamante_attachments_download:
    pattern:        ^/desk/attachments/download/*
    provider:       chain_provider
    anonymous:      true
```

## Contributing

We appreciate any effort to make DiamanteDesk functionality better; therefore, we welcome all kinds of contributions in the form of bug reporting, patches submitting, feature requests or documentation enhancement. Please refer to the DiamanteDesk [guidelines for contributing](http://docs.diamantedesk.com/en/latest/developer-guide/contributing.html) if you wish to be a part of the project.
