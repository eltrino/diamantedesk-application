# DiamanteDesk User Bundle #

User bundle is **required** for the proper work of DiamanteDesk as it contains User Entity.

### Requirements ###

DiamanteDesk supports OroCRM version 1.7+.

### Installation ###

Add as dependency in composer: 

```bash
composer require diamante/user-bundle:dev-master
```

After composer installs this bundle, run this command to update the application:

```bash
php app/console diamante:user:install
```