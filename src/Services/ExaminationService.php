<?php

namespace Platform\Examinations\Services;

use Platform\Examinations\Models\Examination;

/**
 * Thin service layer for the examination catalog CRUD. Shared by Livewire + LLM tools.
 */
class ExaminationService
{
    public function create(array $data): Examination
    {
        if (!isset($data['position'])) {
            $data['position'] = $this->nextPosition((int) $data['team_id']);
        }

        return Examination::create($data);
    }

    public function update(Examination $examination, array $data): Examination
    {
        $examination->update($data);

        return $examination->fresh();
    }

    public function delete(Examination $examination): void
    {
        $examination->delete();
    }

    public function nextPosition(int $teamId): int
    {
        return (int) Examination::forTeam($teamId)->max('position') + 1;
    }
}
