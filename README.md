DiamanteDesk Integration with OroCRM
====================================

This bundle enables **DiamanteDesk** integration with OroCRM. It contains links to all required packages and will install them upon it's own installation.

Installation
============

Install it as a composer dependency:

```bash
composer require diamante/orocrm-integration-bundle:dev-master 
```

For correct installation you'll have to manually run following commands _(this list is a subject to change upon further development)_: 

```bash
php app/console diamante:desk:install
php app/console diamante:user:install
php app/console diamante:embeddedform:install
php app/console assets:install
php app/console assetic:dump
```

Further installation steps:
1. Set up mailer parameters in `parameters.yml` and `System > Configuration > DiamanteDesk > Channels`
2. Enable notifications in `System > Configuration > DiamanteDesk > Notifications` 
