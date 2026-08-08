<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardizedWriteOperations;
use Platform\Examinations\Models\Examination;
use Platform\Examinations\Tools\Concerns\ResolvesExaminationsTeam;

class UpdateExaminationTool implements ToolContract, ToolMetadataContract
{
    use HasStandardizedWriteOperations;
    use ResolvesExaminationsTeam;

    public function getName(): string
    {
        return 'examinations.examinations.PUT';
    }

    public function getDescription(): string
    {
        return 'PUT /examinations - Updates an examination catalog entry. REQUIRED: examination_id. '
            . 'Optional: number, title, category, legal_basis, description, valid_from, valid_until, regulation_label, status.';
    }

    public function getSchema(): array
    {
        return $this->mergeWriteSchema([
            'properties' => [
                'team_id'          => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                'examination_id'   => ['type' => 'integer', 'description' => 'The examination id (REQUIRED).'],
                'number'           => ['type' => 'string'],
                'title'            => ['type' => 'string'],
                'category'         => ['type' => 'string'],
                'legal_basis'      => ['type' => 'string'],
                'description'      => ['type' => 'string'],
                'valid_from'       => ['type' => 'string'],
                'valid_until'      => ['type' => 'string'],
                'regulation_label' => ['type' => 'string'],
                'status'           => ['type' => 'string', 'enum' => ['active', 'archived']],
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

            $payload = [];
            foreach (['number', 'title', 'category', 'legal_basis', 'description', 'valid_from', 'valid_until', 'regulation_label'] as $f) {
                if (array_key_exists($f, $arguments)) {
                    $payload[$f] = $arguments[$f] !== '' ? $arguments[$f] : null;
                }
            }
            if (array_key_exists('title', $payload) && ($payload['title'] === null || trim((string) $payload['title']) === '')) {
                return ToolResult::error('VALIDATION_ERROR', 'title must not be empty.');
            }
            if (array_key_exists('status', $arguments)) {
                if (!in_array($arguments['status'], ['active', 'archived'], true)) {
                    return ToolResult::error('VALIDATION_ERROR', 'status must be active or archived.');
                }
                $payload['status'] = $arguments['status'];
            }

            if (!empty($payload)) {
                $e->update($payload);
            }

            return ToolResult::success([
                'id' => $e->id, 'number' => $e->number, 'title' => $e->title,
                'message' => 'Examination updated successfully.',
            ]);
        } catch (\Throwable $ex) {
            return ToolResult::error('EXECUTION_ERROR', 'Error updating examination: ' . $ex->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => false, 'category' => 'action', 'tags' => ['examinations', 'catalog', 'update'],
            'risk_level' => 'write', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => false,
        ];
    }
}
