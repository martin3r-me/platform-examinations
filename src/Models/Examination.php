<?php

namespace Platform\Examinations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;

/**
 * Examination — ein Eintrag im Untersuchungs-Katalog (DGUV-Grundsatz / arbeitsmedizinische
 * Untersuchung). Referenziert von erbrachten Leistungen (encounter Service) via morphMap
 * (Alias 'examination'). Team-scoped, versioniert.
 *
 * @ai.description Arbeitsmedizinische Untersuchung (DGUV-Grundsatz) als Katalog-Referenz.
 */
class Examination extends Model
{
    use SoftDeletes;

    protected $table = 'examinations';

    protected $fillable = [
        'uuid',
        'team_id',
        'number',
        'title',
        'category',
        'description',
        'legal_basis',
        'status',
        'version',
        'valid_from',
        'valid_until',
        'regulation_label',
        'position',
        'created_by_user_id',
    ];

    protected $casts = [
        'position'    => 'integer',
        'version'     => 'integer',
        'valid_from'  => 'date',
        'valid_until' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                do {
                    $uuid = (string) UuidV7::generate();
                } while (self::where('uuid', $uuid)->exists());
                $model->uuid = $uuid;
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

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'created_by_user_id');
    }

    /** Anzeigename: "G 20 · Lärm" oder nur Titel. */
    public function label(): string
    {
        return $this->number ? trim($this->number . ' · ' . $this->title) : $this->title;
    }

    public function isCurrentlyValid($asOf = null): bool
    {
        $asOf = $asOf ? \Illuminate\Support\Carbon::parse($asOf) : \Illuminate\Support\Carbon::now();
        if ($this->valid_from && $this->valid_from->gt($asOf)) {
            return false;
        }
        if ($this->valid_until && $this->valid_until->lt($asOf)) {
            return false;
        }
        return true;
    }
}
