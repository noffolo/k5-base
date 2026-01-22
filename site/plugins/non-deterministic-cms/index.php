<?php

/**
 * Non-Deterministic CMS Core Plugin
 * Centralizes custom logic, helpers, and extensions to keep the boilerplate clean.
 */

// Load Classes
load([
    'NonDeterministic\\Helpers\\CollectionHelper' => __DIR__ . '/src/Helpers/CollectionHelper.php',
    'NonDeterministic\\Models\\PageLogicTrait'    => __DIR__ . '/src/Models/PageLogicTrait.php',
]);

Kirby::plugin('non-deterministic/cms', [
    'hooks' => [
        // Add hooks here
    ],
]);

// Helper functions are provided by the system or other plugins (e.g. utility-kirby)
