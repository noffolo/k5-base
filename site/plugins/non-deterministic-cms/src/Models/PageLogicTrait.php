<?php

namespace NonDeterministic\Models;

use Kirby\Cms\Structure;
use Kirby\Toolkit\Str;
use NonDeterministic\Helpers\CollectionHelper;

trait PageLogicTrait
{
    public function categoriesOptions(): Structure
    {
        $parent = $this->parent();
        if (!$parent) {
            return new Structure([]);
        }

        $fieldName = 'parent_category_manager';
        $field = $parent->content()->get($fieldName);
        
        if (!$field || $field->isEmpty()) {
            return new Structure([]);
        }

        return $field->toStructure();
    }

    public function formData(): array
    {
        return CollectionHelper::getFormData($this, $this->site());
    }

    public function isExpired(): bool
    {
        $deadline = $this->deadline();
        if ($deadline->isEmpty()) {
            return false;
        }

        return strtotime($deadline->value()) < strtotime('today');
    }

    public function layouts()
    {
        return $this->layout_content()->toLayouts();
    }

    public function isLayoutVisible($layout): bool
    {
        $isExpiredLayout = $layout->scadenza()->isTrue();
        $isExpiredPage   = $this->isExpired();
        $formData        = $this->formData();
        $isAvailable     = $formData['available'] === null || $formData['available'] > 0;

        if ($isExpiredLayout && ($isExpiredPage || !$isAvailable)) {
            return false;
        }

        return true;
    }

    public function seoTitle(): string
    {
        if ($this->isHomePage()) {
            return (string)$this->site()->title();
        }

        return $this->title() . ' | ' . $this->site()->title();
    }

    public function seoDescription(): string
    {
        $desc = $this->descrizione()->isNotEmpty() 
            ? $this->descrizione() 
            : $this->site()->descrizione();
            
        return (string)$desc->cleanText();
    }

    public function seoImage(): ?\Kirby\Cms\File
    {
        return $this->immagine()->toFile() 
            ?? $this->site()->seo_image()->toFile();
    }

    public function seoKeywords(): string
    {
        return (string)($this->tags()->isNotEmpty() ? $this->tags() : $this->site()->tags());
    }
}
