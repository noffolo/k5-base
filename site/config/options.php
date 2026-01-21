<?php
return [
  'site.charset' => 'UTF-8',
  'debug'        => true,   // false in produzione
  'cache'        => false,
  'panel.install'=> false,
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
      'host' => 'authsmtp.securemail.pro', // INSERIRE HOST REALE (es. smtp.googlemail.com)
      'port' => 465,                // 465 per SSL, 587 per TLS
      'security' => true,           // true per SSL, false (o tb 'tls') per TLS
      'auth' => true,
      'username' => 'no-reply@spicgil.it', // INSERIRE UTENTE REALE
      'password' => 'Sp1Frnt2@25',           // INSERIRE PASSWORD REALE
    ]
  ],

  // Form Block Suite: Usa lo stesso indirizzo autenticato per l'invio
  'plain.formblock.from_email' => 'no-reply@spicgil.it',
];
