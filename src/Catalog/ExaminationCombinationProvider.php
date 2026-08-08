<?php

namespace Platform\Examinations\Catalog;

use Platform\Core\Contracts\CatalogCombinationProvider;
use Platform\Examinations\Models\Examination;

/**
 * Liefert der Core-Registry die Vermengungsgruppe eines Untersuchungs-Katalog-Eintrags
 * (morphMap-Alias 'examination').
 */
class ExaminationCombinationProvider implements CatalogCombinationProvider
{
    public function supportedTypes(): array
    {
        return ['examination'];
    }

    public function combinationGroup(string $catalogType, int $catalogId): ?string
    {
        if ($catalogType !== 'examination' || $catalogId <= 0) {
            return null;
        }
        $val = Examination::query()->whereKey($catalogId)->value('combination_group');

        return ($val !== null && $val !== '') ? (string) $val : null;
    }
}
