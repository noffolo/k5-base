<?php
// Removing die()

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\Filesystem\F;

Kirby::plugin('ff3300/panel-logs', [
    'js'  => 'index.js',
    'css' => 'index.css',
    'areas' => [
        'panel-logs' => function ($kirby) {
            return [
                'label' => 'Logs attività',
                'icon'  => 'list-bullet',
                'menu'  => true,
                'link'  => 'panel-logs',
                'views' => [
                    'panel-logs' => [
                        'pattern' => 'panel-logs',
                        'action'  => function () use ($kirby) {
                            return [
                                'component' => 'k-panel-logs-view',
                                'title'     => 'Logs attività',
                                'breadcrumb' => [
                                    [
                                        'label' => 'Logs attività',
                                        'link'  => 'panel-logs'
                                    ]
                                ],
                                'props'     => [
                                    'logs' => array_reverse(F::exists($kirby->root('site') . '/logs/panel_changes.json') ? Data::read($kirby->root('site') . '/logs/panel_changes.json') : [])
                                ]
                            ];
                        }
                    ]
                ]
            ];
        }
    ],
    'api' => [
        'routes' => [
            [
                'pattern' => 'panel-logs/clear',
                'method'  => 'POST',
                'action'  => function () {
                    $kirby = kirby();
                    $file  = $kirby->root('site') . '/logs/panel_changes.json';
                    if (F::exists($file)) {
                        F::write($file, '[]');
                    }
                    return [
                        'status' => 'success'
                    ];
                }
            ]
        ]
    ],
    'hooks' => [
        'page.create:after'       => fn (Page $page) => KirbyPanelLogs::log($page, 'created'),
        'page.update:after'       => fn (Page $newPage, Page $oldPage) => KirbyPanelLogs::log($newPage, 'updated'),
        'page.delete:after'       => fn (Page $page) => KirbyPanelLogs::log($page, 'deleted'),
        'page.changeStatus:after' => fn (Page $newPage, Page $oldPage) => KirbyPanelLogs::log($newPage, 'status changed'),
        'page.changeTitle:after'  => fn (Page $newPage, Page $oldPage) => KirbyPanelLogs::log($newPage, 'title changed'),
        'page.changeSlug:after'   => fn (Page $newPage, Page $oldPage) => KirbyPanelLogs::log($newPage, 'slug changed'),
    ]
]);

class KirbyPanelLogs {
    public static function log(Page $page, string $action) {
        $kirby = kirby();
        $user  = $kirby->user();
        $file  = $kirby->root('site') . '/logs/panel_changes.json';

        $entry = [
            'date'         => date('d-m-Y H:i:s'),
            'user'         => $user ? $user->email() : 'System',
            'action'       => $action,
            'page_title'   => $page->title()->toString(),
            'page_id'      => $page->id(),
            'parent_title' => $page->parent() ? $page->parent()->title()->toString() : 'None',
        ];

        $data = F::exists($file) ? Data::read($file) : [];
        if (!is_array($data)) {
            $data = [];
        }
        $data[] = $entry;

        Data::write($file, $data);
    }
}
