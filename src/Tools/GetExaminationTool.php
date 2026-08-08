<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Examinations\Models\Examination;
use Platform\Examinations\Tools\Concerns\ResolvesExaminationsTeam;

class GetExaminationTool implements ToolContract, ToolMetadataContract
{
    use ResolvesExaminationsTeam;

    public function getName(): string
    {
        return 'examinations.examination.GET';
    }

    public function getDescription(): string
    {
        return 'GET /examinations/{id} - Returns a single examination catalog entry with all fields.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id'        => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                'examination_id' => ['type' => 'integer', 'description' => 'The examination id (REQUIRED).'],
            ],
            'required' => ['examination_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) {
                return $resolved['error'];
            }
            $teamId = (int) $resolved['team_id'];

            $e = Examination::query()->forTeam($teamId)->find((int) ($arguments['examination_id'] ?? 0));
            if (!$e) {
                return ToolResult::error('NOT_FOUND', 'Examination not found.');
            }

            return ToolResult::success([
                'id'               => $e->id,
                'uuid'             => $e->uuid,
                'number'           => $e->number,
                'title'            => $e->title,
                'category'         => $e->category,
                'description'      => $e->description,
                'legal_basis'      => $e->legal_basis,
                'status'           => $e->status,
                'version'          => $e->version,
                'valid_from'       => $e->valid_from?->toDateString(),
                'valid_until'      => $e->valid_until?->toDateString(),
                'currently_valid'  => $e->isCurrentlyValid(),
                'regulation_label' => $e->regulation_label,
                'team_id'          => $e->team_id,
            ]);
        } catch (\Throwable $ex) {
            return ToolResult::error('EXECUTION_ERROR', 'Error loading examination: ' . $ex->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true, 'category' => 'read', 'tags' => ['examinations', 'catalog', 'get'],
            'risk_level' => 'safe', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => true,
        ];
    }
}
