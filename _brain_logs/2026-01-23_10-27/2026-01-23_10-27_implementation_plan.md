# Implementation Plan - Email Config Migration

Migrate SMTP and from-email settings from `site/config/options.php` to the Kirby Panel under the "Impostazioni" tab.

## Proposed Changes

### [Blueprint] [site_configuration.yml](file:///Users/ff3300/Desktop/SITI/k5-spazio13/site/blueprints/tabs/site_configuration.yml)

Add a new section `section_email` with the following fields:
- `smtp_host`: Text field for the SMTP host.
- `smtp_port`: Number field for the SMTP port.
- `smtp_security`: Toggle or Select for SSL/TLS.
- `smtp_user`: Text field for the SMTP username.
- `smtp_pass`: Password field for the SMTP password.
- `from_email`: Text field for the sender email address.

### [Config] [options.php](file:///Users/ff3300/Desktop/SITI/k5-spazio13/site/config/options.php)

Modify the `email` and `plain.formblock.from_email` options to use the `ready` hook. This allows fetching the values from the `$site` object after Kirby has initialized.

```php
'ready' => function ($kirby) {
    return [
        'email' => [
            'transport' => [
                'type'     => 'smtp',
                'host'     => $kirby->site()->smtp_host()->value(),
                'port'     => $kirby->site()->smtp_port()->value(),
                'security' => $kirby->site()->smtp_security()->toBool() ? 'ssl' : false, // Adjust based on field logic
                'auth'     => true,
                'username' => $kirby->site()->smtp_user()->value(),
                'password' => $kirby->site()->smtp_pass()->value(),
            ]
        ],
        'plain.formblock.from_email' => $kirby->site()->from_email()->value() ?: 'no-reply@spazio13.eu',
    ];
}
```

## Verification Plan

### Manual Verification
- Access the Kirby Panel and go to the "Impostazioni" tab.
- Verify that the new "Configurazione Email" section and fields are visible.
- Fill in the fields with the current hardcoded values.
- Verify that the site still functions and (if possible) send a test email through any existing form.
- Check if `plain.formblock.from_email` is correctly picked up by the Form Block Suite (if applicable).
