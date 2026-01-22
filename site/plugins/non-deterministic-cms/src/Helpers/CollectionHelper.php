<?php

namespace NonDeterministic\Helpers;

use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\Structure;
use Kirby\Toolkit\Str;

class CollectionHelper
{
    public static function buildCategoryMarkerMap(Structure $categories): array
    {
        $map = [];

        foreach ($categories as $category) {
            $name = (string)$category->nome();
            $slug = Str::slug($name);

            if ($slug === '') {
                continue;
            }

            $markerUrl = null;
            if ($category->marker()->isNotEmpty()) {
                $markerFile = $category->marker()->toFile();
                if ($markerFile) {
                    $markerUrl = $markerFile->url();
                }
            }

            $map[$slug] = $markerUrl;
        }

        return $map;
    }

    public static function resolveCategoryMarker(array $categoryNames, array $markerMap, ?string $fallback): ?string
    {
        foreach ($categoryNames as $categoryName) {
            $slug = Str::slug((string)$categoryName);

            if ($slug !== '' && !empty($markerMap[$slug])) {
                return $markerMap[$slug];
            }
        }

        return $fallback;
    }

    public static function getLastValidDate(Page $event): ?int
    {
        if ($event->template()->name() === 'calendar-item-from-csv') {
            return (int)$event->content()->get('_date_ts')->value();
        }

        $appointments = $event->appuntamenti()->toStructure();
        $lastAppointment = $appointments ? $appointments->last() : null;

        if ($lastAppointment && $lastAppointment->giorno()->isNotEmpty()) {
            return $lastAppointment->giorno()->toDate();
        }

        return null;
    }

    public static function getOccurrences(Pages $collection): array
    {
        $occurrences = [];

        foreach ($collection as $item) {
            if ($item->template()->name() === 'calendar-item-from-csv') {
                $ts = (int)$item->content()->get('_date_ts')->value();
                $occurrences[] = [
                    'page'      => $item,
                    'timestamp' => $ts,
                    'date_raw'  => $item->date()->value()
                ];
                continue;
            }

            $appointments = $item->appuntamenti()->toStructure();
            if ($appointments->isNotEmpty()) {
                foreach ($appointments as $app) {
                    if ($app->giorno()->isEmpty()) continue;
                    $ts = $app->giorno()->toDate();
                    $occurrences[] = [
                        'page'      => $item,
                        'timestamp' => $ts,
                        'date_raw'  => $app->giorno()->value(),
                        'appointment' => $app
                    ];
                }
            }
        }

        return $occurrences;
    }

    public static function formatDateItalian(?int $timestamp): string
    {
        if (!$timestamp) return '';
        
        $mArr = [
            1 => 'gennaio', 2 => 'febbraio', 3 => 'marzo', 4 => 'aprile',
            5 => 'maggio', 6 => 'giugno', 7 => 'luglio', 8 => 'agosto',
            9 => 'settembre', 10 => 'ottobre', 11 => 'novembre', 12 => 'dicembre'
        ];
        
        $day = date('j', $timestamp);
        $month = $mArr[(int)date('n', $timestamp)] ?? '';
        $year = date('Y', $timestamp);
        
        return "$day $month $year";
    }

    public static function filterByCategories(Pages $collection, array $activeCategories, string $logic): Pages
    {
        if (empty($activeCategories)) {
            return $collection;
        }

        return $collection->filter(function (Page $item) use ($activeCategories, $logic) {
            $itemCategories = array_map(
                static fn(string $category): string => Str::slug($category),
                $item->child_category_selector()->split()
            );

            if ($logic === 'and') {
                return empty(array_diff($activeCategories, $itemCategories));
            }

            return count(array_intersect($activeCategories, $itemCategories)) > 0;
        });
    }

    public static function getFilteredCategories(Pages $collection, Structure $allCategories): Structure
    {
        $selected = [];

        foreach ($collection as $child) {
            foreach ($child->child_category_selector()->split() as $category) {
                $slug = Str::slug($category);

                if (!in_array($slug, $selected, true)) {
                    $selected[] = $slug;
                }
            }
        }

        return $allCategories->filter(
            static fn($category) => in_array(Str::slug($category->nome()), $selected, true)
        );
    }

    public static function getGroupsFromCategories(Structure $categories): array
    {
        return array_values(
            array_unique(
                array_map(
                    static fn($category) => $category->gruppo()->value(),
                    iterator_to_array($categories)
                )
            )
        );
    }

    public static function getLocationsArray(
        Pages $collection,
        array $categoryMarkerMap,
        ?File $defaultMarker,
        array $activeCategories,
        string $filterLogic
    ): array {
        $filtered = self::filterByCategories($collection, $activeCategories, $filterLogic);

        $locations = [];

        $defaultMarkerUrl = $defaultMarker ? $defaultMarker->url() : null;

        foreach ($filtered as $item) {
            $location = $item->locator()->toLocation();
            $marker = self::resolveCategoryMarker(
                $item->child_category_selector()->split(','),
                $categoryMarkerMap,
                $defaultMarkerUrl
            );

            if ($location && $location->lat() && $location->lon() && $marker) {
                $locations[] = [
                    'title' => $item->title()->value(),
                    'lat' => $location->lat(),
                    'lon' => $location->lon(),
                    'url' => $item->url(),
                    'marker' => $marker,
                ];
            }
        }

        return $locations;
    }

    public static function getFormData(
        Page $formPage,
        Site $site,
        ?Pages $responses = null,
        bool $restrictToDescendants = true
    ): array {
        $responses ??= $restrictToDescendants ? $formPage->index(true) : $site->index(true);

        $responses = $responses->filter(function (Page $response) use ($formPage, $restrictToDescendants) {
            if ($response->intendedTemplate()->name() !== 'formrequest') {
                return false;
            }

            if ($restrictToDescendants) {
                return $response->isDescendantOf($formPage) || Str::startsWith($response->id(), $formPage->id());
            }

            return Str::startsWith($response->id(), $formPage->id());
        });

        $responsesRead = $responses->filter(static fn(Page $response) => $response->content()->get('read')->isNotEmpty());

        $count = $responsesRead->count();
        $max = $formPage->num_max()->isNotEmpty() ? (int) $formPage->num_max()->value() : null;
        $available = $max !== null ? max(0, $max - $count) : null;

        $percent = 0;
        if ($max && $max > 0) {
            $raw = ($count / $max) * 100;
            $percent = $count > 0 ? max(5, min(100, $raw)) : 0;
        }

        return compact('responses', 'responsesRead', 'count', 'max', 'available', 'percent');
    }

    public static function formDataFor(?Page $context = null): array
    {
        if ($context instanceof Page) {
            $site = App::instance()->site();

            return [
                'formData' => static function (?Page $formPage = null) use ($context, $site): array {
                    return self::getFormData($formPage ?? $context, $site);
                },
            ];
        }

        return [
            'formData' => static function (?Page $formPage = null): array {
                unset($formPage);

                return [
                    'responses' => new Collection(),
                    'responsesRead' => new Collection(),
                    'count' => 0,
                    'max' => null,
                    'available' => null,
                    'percent' => 0,
                ];
            },
        ];
    }
}
