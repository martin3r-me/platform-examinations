<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Examinations\Models\ExaminationBundle;

class ListBundlesTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'examinations.bundles.GET';
    }

    public function getDescription(): string
    {
        return 'GET /examinations/bundles - Listet die Untersuchungs-Bündel des Teams mit Anzahl enthaltener Untersuchungen.';
    }

    public function getSchema(): array
    {
        return ['type' => 'object', 'properties' => [], 'required' => []];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        $teamId = $context->team?->id ?? $context->user?->currentTeam?->id;
        if (!$teamId) {
            return ToolResult::error('NO_TEAM', 'Kein Team im Kontext.');
        }

        $bundles = ExaminationBundle::forTeam((int) $teamId)->withCount('examinations')
            ->orderBy('position')->orderByDesc('id')->get()
            ->map(fn ($b) => [
                'id'            => $b->id,
                'name'          => $b->name,
                'status'        => $b->status,
                'examinations'  => $b->examinations_count,
            ])->all();

        return ToolResult::success(['data' => $bundles, 'total' => count($bundles)]);
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true, 'category' => 'query', 'tags' => ['examinations', 'bundle', 'list'],
            'risk_level' => 'read', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => true,
        ];
    }
}
