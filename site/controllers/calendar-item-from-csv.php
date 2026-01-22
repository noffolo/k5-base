<?php

use function Site\Helpers\Collection\formDataFor;

return function ($page, $site, $kirby) {
    return [
        // Forniamo formData (fallback vuoto) per evitare errori nel snippet layouts
    ] + formDataFor();
};
