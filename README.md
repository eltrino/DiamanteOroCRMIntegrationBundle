#DiamanteDesk Integration with OroCRM

This bundle enables **DiamanteDesk** integration with OroCRM. It contains links to all packages required for the proper installation of the help desk.

## Installation

###Installation via Marketplace

Navigate to `System > Package Manager` to install it from OroCRM Marketplace.

###Installation via Composer

Install the integration bundle as a composer dependency:

```bash
php composer.phar require diamante/orocrm-integration-bundle
```

In order to install DiamanteDesk correctly, manually run the following commands _(this list may be changed upon further development)_: 

```bash
php app/console diamante:desk:install
php app/console diamante:user:schema
php app/console diamante:embeddedform:schema
php app/console assets:install
```
Further installation steps:

1. Set up mailer parameters in `parameters.yml` and at _System > Configuration > DiamanteDesk > Channels_.
2. Enable notifications at _System > Configuration > DiamanteDesk > Notifications_. 

## Contributing

We appreciate any effort to make DiamanteDesk functionality better; therefore, we welcome all kinds of contributions in the form of bug reporting, patches submitting, feature requests or documentation enhancement. Please refer to the DiamanteDesk guidelines for contributing if you wish to be a part of the project.