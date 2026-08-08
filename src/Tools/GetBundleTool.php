<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Examinations\Models\ExaminationBundle;

class GetBundleTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'examinations.bundle.GET';
    }

    public function getDescription(): string
    {
        return 'GET /examinations/bundle - Zeigt ein Bündel mit seinen Untersuchungen (Nummer, Titel, Vermengungsgruppe). REQUIRED: bundle_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'bundle_id' => ['type' => 'integer', 'description' => 'ID des Bündels (REQUIRED).'],
            ],
            'required' => ['bundle_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        $teamId = $context->team?->id ?? $context->user?->currentTeam?->id;
        if (!$teamId) {
            return ToolResult::error('NO_TEAM', 'Kein Team im Kontext.');
        }

        $bundle = ExaminationBundle::forTeam((int) $teamId)->with('examinations')->find((int) ($arguments['bundle_id'] ?? 0));
        if (!$bundle) {
            return ToolResult::error('NOT_FOUND', 'Bündel nicht gefunden.');
        }

        return ToolResult::success([
            'id'           => $bundle->id,
            'name'         => $bundle->name,
            'description'  => $bundle->description,
            'status'       => $bundle->status,
            'examinations' => $bundle->examinations->map(fn ($e) => [
                'id'                => $e->id,
                'number'            => $e->number,
                'title'             => $e->title,
                'combination_group' => $e->combination_group,
            ])->all(),
        ]);
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true, 'category' => 'query', 'tags' => ['examinations', 'bundle', 'get'],
            'risk_level' => 'read', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => true,
        ];
    }
}
