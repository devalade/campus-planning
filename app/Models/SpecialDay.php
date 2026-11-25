<?php

namespace App\Models;

use Database\Factories\SpecialDayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $date Date au format Y-m-d, par exemple 2026-11-11
 * @property string $label
 * @property int|null $created_by
 */
#[Fillable(['date', 'label', 'created_by'])]
class SpecialDay extends Model
{
    /** @use HasFactory<SpecialDayFactory> */
    use HasFactory;

    /**
     * Get the special days between Monday and Saturday of the week that starts on the given Monday (Y-m-d), keyed by date.
     *
     * @return Collection<string, SpecialDay>
     */
    public static function inWeekOf(string $monday): Collection
    {
        $saturday = date('Y-m-d', strtotime($monday.' +5 days'));

        return self::query()
            ->whereBetween('date', [$monday, $saturday])
            ->get()
            ->keyBy('date');
    }
}
