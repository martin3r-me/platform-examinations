<?php

namespace Platform\Examinations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;

/**
 * Ein Bündel (Paket) von Untersuchungen. Vorlage/Auswahl-Hilfe: beim Erfassen im Termin
 * werden die einzelnen Untersuchungen als separate Leistungen angelegt — das Bündel selbst
 * ist kein Leistungstyp. Grundlage für spätere Preislogik (Bündel = Produkt).
 */
class ExaminationBundle extends Model
{
    use SoftDeletes;

    protected $table = 'examination_bundles';

    protected $fillable = [
        'uuid', 'team_id', 'name', 'description', 'status', 'position', 'created_by_user_id',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) UuidV7::generate();
            }
            if (empty($model->team_id) && auth()->check()) {
                $model->team_id = auth()->user()->currentTeam?->id;
            }
        });
    }

    public function scopeForTeam($query, int $teamId)
    {
        return $query->where($this->getTable() . '.team_id', $teamId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function examinations(): BelongsToMany
    {
        return $this->belongsToMany(Examination::class, 'examination_bundle_items')
            ->withPivot('position')
            ->orderBy('examination_bundle_items.position');
    }
}
