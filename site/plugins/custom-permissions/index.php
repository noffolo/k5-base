<?php

use Kirby\Cms\Page;
use Kirby\Exception\PermissionException;

Kirby::plugin('ff3300/custom-permissions', [
    'userMethods' => [
        'hasPagePermission' => function (Page $page) {
            // Admin has full access
            if ($this->role()->id() === 'admin') {
                return true;
            }

            // Only editor role is handled here. 
            // If you add more roles, adjust accordingly.
            if ($this->role()->id() !== 'editor') {
                return false;
            }

            $allowedPages = $this->content()->allowed_pages()->toPages()->pluck('uuid');
            $allowedCollections = $this->content()->allowed_collections()->toPages()->pluck('uuid');

            // 1. Check if the page itself is explicitly allowed
            if (in_array($page->uuid()->toString(), $allowedPages)) {
                return true;
            }

            // 2. Check if the page or any of its ancestors are in the allowed collections
            $parents = $page->parents()->append($page); // Include self in collection check if needed, 
                                                       // but usually collection means parent + children.
                                                       // If we want parent itself to be editable if selected as collection:
            foreach ($parents as $p) {
                if (in_array($p->uuid()->toString(), $allowedCollections)) {
                    return true;
                }
            }

            return false;
        }
    ],
    'hooks' => [
        'page.update:before' => function (Page $page) {
            if ($user = $this->user()) {
                if (!$user->hasPagePermission($page)) {
                    throw new PermissionException('Non hai i permessi per modificare questa pagina.');
                }
            }
        },
        'page.create:before' => function (Page $page, string $template, array $content) {
            // Note: for creation, $page is the parent page.
            if ($user = $this->user()) {
                if (!$user->hasPagePermission($page)) {
                    throw new PermissionException('Non hai i permessi per creare pagine qui.');
                }
            }
        },
        'page.delete:before' => function (Page $page) {
            if ($user = $this->user()) {
                if (!$user->hasPagePermission($page)) {
                    throw new PermissionException('Non hai i permessi per eliminare questa pagina.');
                }
            }
        },
        'page.changeStatus:before' => function (Page $page) {
            if ($user = $this->user()) {
                if (!$user->hasPagePermission($page)) {
                    throw new PermissionException('Non hai i permessi per cambiare lo stato di questa pagina.');
                }
            }
        },
        'page.changeSlug:before' => function (Page $page) {
            if ($user = $this->user()) {
                if (!$user->hasPagePermission($page)) {
                    throw new PermissionException('Non hai i permessi per cambiare l\'URL di questa pagina.');
                }
            }
        },
        'page.changeTitle:before' => function (Page $page) {
            if ($user = $this->user()) {
                if (!$user->hasPagePermission($page)) {
                    throw new PermissionException('Non hai i permessi per cambiare il titolo di questa pagina.');
                }
            }
        },
        'page.sort:before' => function (Page $page) {
            if ($user = $this->user()) {
                if (!$user->hasPagePermission($page)) {
                    throw new PermissionException('Non hai i permessi per riordinare questa pagina.');
                }
            }
        },
        'user.update:before' => function ($newUser, $oldUser) {
            $currentUser = $this->user();
            if ($currentUser && $currentUser->role()->id() !== 'admin') {
                // Se un non-admin tenta di modificare questi campi (es. nel proprio profilo), blocca l'operazione.
                $pNew = $newUser->content()->allowed_pages()->value();
                $pOld = $oldUser->content()->allowed_pages()->value();
                $cNew = $newUser->content()->allowed_collections()->value();
                $cOld = $oldUser->content()->allowed_collections()->value();

                if ($pNew !== $pOld || $cNew !== $cOld) {
                    throw new PermissionException('Solo l\'amministratore può modificare i permessi.');
                }
            }
        }
    ]
]);
