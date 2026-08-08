<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Examinations\Models\ExaminationBundle;

class CreateBundleTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'examinations.bundles.POST';
    }

    public function getDescription(): string
    {
        return 'POST /examinations/bundles - Legt ein Untersuchungs-Bündel (Paket) an. REQUIRED: name. '
            . 'Optional: description, status (active|archived). Untersuchungen danach mit examinations.bundle_items.POST zuordnen.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name'        => ['type' => 'string', 'description' => 'Name des Bündels (REQUIRED), z.B. "Einstellung Metallbau".'],
                'description' => ['type' => 'string', 'description' => 'Optionale Beschreibung.'],
                'status'      => ['type' => 'string', 'enum' => ['active', 'archived'], 'description' => 'Default active.'],
            ],
            'required' => ['name'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        $teamId = $context->team?->id ?? $context->user?->currentTeam?->id;
        if (!$teamId) {
            return ToolResult::error('NO_TEAM', 'Kein Team im Kontext.');
        }
        $name = trim((string) ($arguments['name'] ?? ''));
        if ($name === '') {
            return ToolResult::error('VALIDATION_ERROR', 'name ist erforderlich.');
        }

        $bundle = ExaminationBundle::create([
            'team_id'            => (int) $teamId,
            'name'               => $name,
            'description'        => isset($arguments['description']) && $arguments['description'] !== '' ? (string) $arguments['description'] : null,
            'status'             => ($arguments['status'] ?? 'active') === 'archived' ? 'archived' : 'active',
            'created_by_user_id' => $context->user?->id,
        ]);

        return ToolResult::success(['id' => $bundle->id, 'name' => $bundle->name, 'status' => $bundle->status]);
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false, 'category' => 'action', 'tags' => ['examinations', 'bundle', 'create'],
            'risk_level' => 'write', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => false,
        ];
    }
}
