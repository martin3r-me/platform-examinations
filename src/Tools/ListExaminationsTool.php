<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Tools\Concerns\HasStandardGetOperations;
use Platform\Examinations\Models\Examination;
use Platform\Examinations\Tools\Concerns\ResolvesExaminationsTeam;

class ListExaminationsTool implements ToolContract, ToolMetadataContract
{
    use HasStandardGetOperations;
    use ResolvesExaminationsTeam;

    public function getName(): string
    {
        return 'examinations.examinations.GET';
    }

    public function getDescription(): string
    {
        return 'GET /examinations - Lists the occupational-health examination catalog (DGUV Grundsätze). '
            . 'Params: team_id (optional), category, search, sort/limit/offset.';
    }

    public function getSchema(): array
    {
        return $this->mergeSchemas($this->getStandardGetSchema(), [
            'properties' => [
                'team_id'  => ['type' => 'integer', 'description' => 'Optional: team id. Default: current team.'],
                'category' => ['type' => 'string', 'description' => 'Optional: filter by category.'],
                'search'   => ['type' => 'string', 'description' => 'Optional: search title/number/legal_basis.'],
            ],
        ]);
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) {
                return $resolved['error'];
            }
            $teamId = (int) $resolved['team_id'];

            $query = Examination::query()->forTeam($teamId);

            if (!empty($arguments['category'])) {
                $query->where('category', $arguments['category']);
            }
            if (!empty($arguments['search'])) {
                $s = $arguments['search'];
                $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")
                    ->orWhere('number', 'like', "%{$s}%")->orWhere('legal_basis', 'like', "%{$s}%"));
            }

            $this->applyStandardFilters($query, $arguments, ['category', 'status', 'created_at']);
            $this->applyStandardSort($query, $arguments, ['position', 'number', 'title', 'created_at'], 'position', 'asc');

            $result = $this->applyStandardPaginationResult($query, $arguments);

            $data = collect($result['data'])->map(fn (Examination $e) => [
                'id'          => $e->id,
                'number'      => $e->number,
                'title'       => $e->title,
                'category'    => $e->category,
                'legal_basis' => $e->legal_basis,
                'status'      => $e->status,
                'team_id'     => $e->team_id,
            ])->values()->toArray();

            return ToolResult::success(['data' => $data, 'pagination' => $result['pagination'] ?? null, 'team_id' => $teamId]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Error loading examinations: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true, 'category' => 'read', 'tags' => ['examinations', 'catalog', 'list'],
            'risk_level' => 'safe', 'requires_auth' => true, 'requires_team' => true, 'idempotent' => true,
        ];
    }
}
