<?php
return [
  'site.charset' => 'UTF-8',
  'debug'        => true,   // false in produzione
  'cache'        => false,
  'panel.install'=> true,
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

  'ready' => function ($kirby) {
    return [
      // Configurazione Email (SMTP)
      // Carica i valori impostati nel Panel (tab Impostazioni)
      'email' => [
        'transport' => [
          'type'      => 'smtp',
          'host'      => $kirby->site()->smtp_host()->value(),
          'port'      => $kirby->site()->smtp_port()->value(),
          'security'  => $kirby->site()->smtp_security()->toBool() ? 'ssl' : false,
          'auth'      => true,
          'username'  => $kirby->site()->smtp_user()->value(),
          'password'  => $kirby->site()->smtp_pass()->value(),
        ]
      ],

      // Form Block Suite: Usa lo stesso indirizzo autenticato per l'invio
      'plain.formblock.from_email' => $kirby->site()->from_email()->value() ?: 'no-reply@' . parse_url($kirby->url(), PHP_URL_HOST),
    ];
  }
];
