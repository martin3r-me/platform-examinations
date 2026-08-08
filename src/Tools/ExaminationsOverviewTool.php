<?php

namespace Platform\Examinations\Tools;

use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class ExaminationsOverviewTool implements ToolContract, ToolMetadataContract
{
    public function getName(): string
    {
        return 'examinations.overview.GET';
    }

    public function getDescription(): string
    {
        return 'GET /examinations/overview - Module concept + taxonomy for the examinations catalog (DGUV Grundsätze). '
            . 'Examinations are the reference layer that erbrachte Leistungen (encounter Service) bind to via morphMap (catalog alias "examination").';
    }

    public function getSchema(): array
    {
        return ['type' => 'object', 'properties' => []];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        return ToolResult::success([
            'module' => 'examinations',
            'concepts' => [
                'examination' => [
                    'model'       => 'Platform\\Examinations\\Models\\Examination',
                    'table'       => 'examinations',
                    'morph_alias' => 'examination',
                    'key_fields'  => ['id', 'uuid', 'number', 'title', 'category', 'legal_basis', 'status', 'version', 'team_id'],
                    'note'        => 'One record = one occupational-health examination (DGUV Grundsatz). Referenced by encounter Service (erbrachte Leistung).',
                ],
            ],
            'taxonomy' => [
                'category' => config('examinations.categories', []),
                'status'   => ['active', 'archived'],
            ],
            'related_tools' => [
                'list'   => 'examinations.examinations.GET',
                'get'    => 'examinations.examination.GET',
                'create' => 'examinations.examinations.POST',
                'update' => 'examinations.examinations.PUT',
                'delete' => 'examinations.examinations.DELETE',
            ],
        ]);
    }

    public function getMetadata(): array
    {
        return [
            'read_only' => true, 'category' => 'read', 'tags' => ['examinations', 'catalog', 'overview'],
            'risk_level' => 'safe', 'requires_auth' => false, 'requires_team' => false, 'idempotent' => true,
        ];
    }
}
