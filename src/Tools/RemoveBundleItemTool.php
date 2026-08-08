<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Examinations\Models\ExaminationBundle;

class RemoveBundleItemTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'examinations.bundle_items.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /examinations/bundle_items - Entfernt eine Untersuchung aus einem Bündel. REQUIRED: bundle_id, examination_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'bundle_id'      => ['type' => 'integer', 'description' => 'ID des Bündels (REQUIRED).'],
                'examination_id' => ['type' => 'integer', 'description' => 'ID der Untersuchung (REQUIRED).'],
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

        $removed = $bundle->examinations()->detach((int) ($arguments['examination_id'] ?? 0));

        return ToolResult::success(['bundle_id' => $bundle->id, 'removed' => $removed]);
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false, 'category' => 'action', 'tags' => ['examinations', 'bundle', 'item', 'delete'],
            'risk_level' => 'write', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => true,
        ];
    }
}
