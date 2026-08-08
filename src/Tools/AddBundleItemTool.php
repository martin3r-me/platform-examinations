<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Examinations\Models\Examination;
use Platform\Examinations\Models\ExaminationBundle;

class AddBundleItemTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'examinations.bundle_items.POST';
    }

    public function getDescription(): string
    {
        return 'POST /examinations/bundle_items - Ordnet einem Bündel eine Untersuchung zu. REQUIRED: bundle_id, examination_id. '
            . 'Optional: position (Default: ans Ende). Ein Bündel darf nur EINE Vermengungsgruppe enthalten (wird geprüft).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'bundle_id'      => ['type' => 'integer', 'description' => 'ID des Bündels (REQUIRED).'],
                'examination_id' => ['type' => 'integer', 'description' => 'ID der Untersuchung (REQUIRED).'],
                'position'       => ['type' => 'integer', 'description' => 'Optionale Reihenfolge (Default: ans Ende).'],
            ],
            'required' => ['bundle_id', 'examination_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        $teamId = $context->team?->id ?? $context->user?->currentTeam?->id;
        if (!$teamId) {
            return ToolResult::error('NO_TEAM', 'Kein Team im Kontext.');
        }

        $bundle = ExaminationBundle::forTeam((int) $teamId)->find((int) ($arguments['bundle_id'] ?? 0));
        if (!$bundle) {
            return ToolResult::error('NOT_FOUND', 'Bündel nicht gefunden.');
        }

        $examination = Examination::forTeam((int) $teamId)->find((int) ($arguments['examination_id'] ?? 0));
        if (!$examination) {
            return ToolResult::error('NOT_FOUND', 'Untersuchung nicht gefunden.');
        }

        // Vermengungsregel: ein Bündel darf nur EINE nicht-leere Gruppe enthalten.
        $newGroup = $examination->combination_group;
        if ($newGroup) {
            $existing = $bundle->examinations()->pluck('combination_group')->filter()->unique();
            if ($existing->isNotEmpty() && !$existing->contains($newGroup)) {
                return ToolResult::error('COMBINATION_CONFLICT',
                    "Untersuchung gehört zur Gruppe '{$newGroup}', das Bündel enthält bereits '{$existing->implode(', ')}'. Ein Bündel darf nur eine Vermengungsgruppe enthalten.");
            }
        }

        $position = isset($arguments['position']) && (int) $arguments['position'] > 0
            ? (int) $arguments['position']
            : ($bundle->examinations()->count() + 1);

        $bundle->examinations()->syncWithoutDetaching([$examination->id => ['position' => $position]]);

        return ToolResult::success([
            'bundle_id'      => $bundle->id,
            'examination_id' => $examination->id,
            'position'       => $position,
            'label'          => $examination->label(),
        ]);
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false, 'category' => 'action', 'tags' => ['examinations', 'bundle', 'item', 'create'],
            'risk_level' => 'write', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => false,
        ];
    }
}
