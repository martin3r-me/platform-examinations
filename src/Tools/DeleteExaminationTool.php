<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Examinations\Models\Examination;
use Platform\Examinations\Tools\Concerns\ResolvesExaminationsTeam;

class DeleteExaminationTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesExaminationsTeam;

    public function getName(): string
    {
        return 'examinations.examinations.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /examinations - Deletes an examination catalog entry. REQUIRED: examination_id.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id'        => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                'examination_id' => ['type' => 'integer', 'description' => 'The examination id (REQUIRED).'],
            ],
            'required' => ['examination_id'],
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

            $e = Examination::query()->forTeam($teamId)->find((int) ($arguments['examination_id'] ?? 0));
            if (!$e) {
                return ToolResult::error('NOT_FOUND', 'Examination not found.');
            }
            $e->delete();

            return ToolResult::success(['id' => (int) ($arguments['examination_id']), 'message' => 'Examination deleted successfully.']);
        } catch (\Throwable $ex) {
            return ToolResult::error('EXECUTION_ERROR', 'Error deleting examination: ' . $ex->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false, 'category' => 'action', 'tags' => ['examinations', 'catalog', 'delete'],
            'risk_level' => 'write', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => true,
        ];
    }
}
