<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Examinations\Models\Examination;
use Platform\Examinations\Services\ExaminationService;
use Platform\Examinations\Tools\Concerns\ResolvesExaminationsTeam;

class CreateExaminationTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesExaminationsTeam;

    public function getName(): string
    {
        return 'examinations.examinations.POST';
    }

    public function getDescription(): string
    {
        return 'POST /examinations - Creates an occupational-health examination catalog entry (DGUV Grundsatz). '
            . 'REQUIRED: title. Optional: number (e.g. "G 20"), category, legal_basis, description, '
            . 'valid_from, valid_until, regulation_label, status (default active).';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id'          => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                'number'           => ['type' => 'string', 'description' => 'Optional: DGUV number, e.g. "G 20".'],
                'title'            => ['type' => 'string', 'description' => 'Short title, e.g. "Lärm" (REQUIRED).'],
                'category'         => ['type' => 'string', 'description' => 'Optional: grouping category.'],
                'legal_basis'      => ['type' => 'string', 'description' => 'Optional: e.g. "DGUV Grundsatz G 20".'],
                'description'      => ['type' => 'string', 'description' => 'Optional: notes.'],
                'valid_from'       => ['type' => 'string', 'description' => 'Optional: valid-from date (YYYY-MM-DD).'],
                'valid_until'      => ['type' => 'string', 'description' => 'Optional: valid-until date (YYYY-MM-DD).'],
                'regulation_label' => ['type' => 'string', 'description' => 'Optional: e.g. "DGUV Stand 2023".'],
                'status'           => ['type' => 'string', 'enum' => ['active', 'archived'], 'description' => 'Optional: default active.'],
            ],
            'required' => ['title'],
        ]);
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            if (!$context->user) {
                return ToolResult::error('AUTH_ERROR', 'No user in context.');
            }
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) {
                return $resolved['error'];
            }
            $teamId = (int) $resolved['team_id'];

            $title = trim((string) ($arguments['title'] ?? ''));
            if ($title === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title is required.');
            }
            $status = $arguments['status'] ?? 'active';
            if (!in_array($status, ['active', 'archived'], true)) {
                return ToolResult::error('VALIDATION_ERROR', 'status must be active or archived.');
            }

            $val = fn ($k) => isset($arguments[$k]) && $arguments[$k] !== '' ? trim((string) $arguments[$k]) : null;

            $examination = (new ExaminationService())->create([
                'team_id'            => $teamId,
                'created_by_user_id' => $context->user->id,
                'number'             => $val('number'),
                'title'              => $title,
                'category'           => $val('category'),
                'legal_basis'        => $val('legal_basis'),
                'description'        => $val('description'),
                'valid_from'         => $val('valid_from'),
                'valid_until'        => $val('valid_until'),
                'regulation_label'   => $val('regulation_label'),
                'status'             => $status,
            ]);

            return ToolResult::success([
                'id'      => $examination->id,
                'uuid'    => $examination->uuid,
                'number'  => $examination->number,
                'title'   => $examination->title,
                'team_id' => $examination->team_id,
                'message' => "Examination '{$examination->label()}' created successfully.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error creating examination: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false, 'category' => 'action', 'tags' => ['examinations', 'catalog', 'create'],
            'risk_level' => 'write', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => false,
        ];
    }
}
