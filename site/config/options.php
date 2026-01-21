<?php
return [
  'site.charset' => 'UTF-8',
  'debug'        => true,   // false in produzione
  'cache'        => false,
<<<<<<< HEAD
  'panel.install'=> true,
=======
  'panel.install'=> false,
>>>>>>> 30a41e65c710913fdd28c19b9aef2cba21432ae0
  'languages'    => true,
  'locale'       => 'it_IT.utf8',

  // Thumbs (K5) — ripulito da residui K4
  'thumbs' => [
    'driver'  => 'gd',     // usa GD; se vuoi Imagick: 'imagick'
    'format'  => 'webp',
    'srcsets' => [
      'default' => [
        '800w'  => ['width' => 800,  'quality' => 66],
        '1280w' => ['width' => 1280, 'quality' => 75],
      ]
    ],
  ],

  // Opzioni mappa sito (usate dai routes)
  'sitemap.ignore' => ['error'],

  // Configurazione Email (SMTP)
  // IMPORTANTE: Sostituire con i parametri reali del provider di posta
  'email' => [
    'transport' => [
      'type' => 'smtp',
<<<<<<< HEAD
      'host' => 'smtps.aruba.it', // INSERIRE HOST REALE (es. smtp.googlemail.com)
      'port' => 465,                // 465 per SSL, 587 per TLS
      'security' => true,           // true per SSL, false (o tb 'tls') per TLS
      'auth' => true,
      'username' => 'no-reply@spazio13.eu', // INSERIRE UTENTE REALE
      'password' => 'SpazioAdmin13!',           // INSERIRE PASSWORD REALE
=======
      'host' => 'authsmtp.securemail.pro', // INSERIRE HOST REALE (es. smtp.googlemail.com)
      'port' => 465,                // 465 per SSL, 587 per TLS
      'security' => true,           // true per SSL, false (o tb 'tls') per TLS
      'auth' => true,
      'username' => 'no-reply@spicgil.it', // INSERIRE UTENTE REALE
      'password' => 'Sp1Frnt2@25',           // INSERIRE PASSWORD REALE
>>>>>>> 30a41e65c710913fdd28c19b9aef2cba21432ae0
    ]
  ],

  // Form Block Suite: Usa lo stesso indirizzo autenticato per l'invio
<<<<<<< HEAD
  'plain.formblock.from_email' => 'no-reply@spazio13.eu',
=======
  'plain.formblock.from_email' => 'no-reply@spicgil.it',
>>>>>>> 30a41e65c710913fdd28c19b9aef2cba21432ae0
];
