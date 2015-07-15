# DiamanteDesk Bundle #

DiamanteDesk software extends base OroCRM functionality. Its main purpose is to integrate customer support system into the Client's CRM system.

Using this comprehensive help desk, it is now possible to create support tickets within the CRM and associate them with any customer from the system. In DiamanteDesk, tickets can be assigned to specific users and grouped into Branches. This option allows grouping tickets related to a certain customer.

Currently, DiamanteDesk software is in alpha version. Eltrino team is making efforts to improve and increase the amount of features available to a user.

### Requirements ###

DiamanteDesk supports OroCRM version 1.7+.

### Installation ###

Add as dependency in composer:

```bash
composer require diamante/desk-bundle:dev-master
```

In addition, you will need to run DiamanteDesk internal command to install the software:

```bash
php app/console diamante:desk:install
```

And here is a command that updates already installed software:

```bash
php app/console diamante:desk:update
```

After *install* or *update* commands the last thing which should be done is assets installation. It should be done in Symfony in a standard way:

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