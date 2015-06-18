# DiamanteDesk Email Processing Bundle #

All the emails that get to the support email address are automatically retrieved by the DiamanteDesk system in order to create a  separate ticket out of each received letter. EmailProcessing Bundle is required for the proper work of this functionality.

### Requirements ###

DiamanteDesk supports OroCRM version 1.7+.

### Installation ###

Add as dependency in composer:

```bash
composer require diamante/email-processing-bundle:dev-master
```
### Usage ###

To start email processing, run this command on your console:
```bash
php app/console diamante:emailprocessing:  pipe <  /path/to/emails/test-email.eml
```

This command is only applicable when DiamanteDesk and email server are installed on the same server machine. It includes piping method that has a remarkable advantage over a regular IMAP, as when using piping, all the emails get to the system and are converted into tickets or comments immediately, unlike when using IMAP which sends polls to the remote server within scheduled time (at least 1 minute).