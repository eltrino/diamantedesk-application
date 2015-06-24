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

You can also run and configure email processing from the console. Two commands are available:

* using IMAP protocol for email retrieval from a remote email server.

        php app/console oro:cron:diamante:emailprocessing:general

This command may be configured through a crontab, allowing to send polls periodically on a given schedule.

* or using the email piping method:

         php app/console diamante:emailprocessing:pipe <  /path/to/emails/stream

This command is only applicable when DiamanteDesk and email server are installed on the same server machine. It includes piping method which has a remarkable advantage over a IMAP, as,  when using piping, all the emails get to the system and are converted into tickets or comments **immediately**, unlike when using IMAP which sends polls to the remote server within scheduled time (at least 1 minute). This advantage allows to quicky react on customer requests or solve any issues on a real-time basis.