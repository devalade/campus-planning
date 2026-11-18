<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SpecialDayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property CarbonImmutable $date
 * @property string $label
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['date', 'label', 'created_by'])]
class SpecialDay extends Model
{
    /** @use HasFactory<SpecialDayFactory> */
    use HasFactory;

    /**
     * Normalize the date before storing it and return it as an immutable date.
     *
     * @return Attribute<CarbonImmutable, string>
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): CarbonImmutable => CarbonImmutable::parse($value)->startOfDay(),
            set: fn (mixed $value): string => CarbonImmutable::parse($value)->toDateString(),
        );
    }

    /**
     * Get the special days between Monday and Saturday of the given week, keyed by date.
     *
     * @return Collection<string, SpecialDay>
     */
    public static function inWeekOf(CarbonImmutable $weekStart): Collection
    {
        return self::query()
            ->whereBetween('date', [$weekStart->toDateString(), $weekStart->addDays(5)->toDateString()])
            ->get()
            ->keyBy(fn (SpecialDay $specialDay): string => $specialDay->date->toDateString());
    }
}
