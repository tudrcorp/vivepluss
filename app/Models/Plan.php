<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Plan extends Model
{
    protected $connection = 'mysql_vivepluss';

    protected $table = 'plans';

    protected $fillable = [
        'business_unit_id',
        'code',
        'description',
        'status',
        'created_by',
        'type',
    ];

    /**
     * Planes que se pueden ofrecer en una cotización: los activos que tienen al
     * menos una tarifa con precio.
     *
     * La sincronización desde Integracorp (`AssignedPlanCatalogSync`) solo crea
     * tarifas locales para lo que tiene neta pactada en la matriz de
     * negociación, así que "tiene tarifas" equivale a "el analista ya le puso
     * precio de venta". Un plan asignado pero todavía sin netas llega al
     * catálogo con sus beneficios y no se ofrece: sin precio no hay nada que
     * cotizar.
     *
     * @param  Builder<Plan>  $query
     */
    public function scopeCotizable(Builder $query): void
    {
        $query->where('type', 'BASICO')
            ->where(function (Builder $builder): void {
                $builder->whereNull('status')->orWhere('status', 'ACTIVO');
            })
            ->whereHas('fees');
    }

    /**
     * Tarifas del plan. `fees.plan_id` es la columna canónica del catálogo.
     */
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'plan_id', 'id');
    }

    /**
     * Get all of the comments for the Plan
     */
    public function benefits(): HasMany
    {
        return $this->hasMany(Benefit::class, 'plan_id', 'id');
    }

    /**
     * The servicios that belong to the User
     */
    public function benefitPlans(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class, 'benefit_plans')
            ->using(BenefitPlan::class)
            ->withPivot(['description']);
    }

    /**
     * The servicios that belong to the User
     */
    public function coveragePlans(): BelongsToMany
    {
        return $this->belongsToMany(Coverage::class, 'coverage_plans')
            ->using(CoveragePlan::class)
            ->withPivot(['price']);
    }

    /**
     * The servicios that belong to the User
     */
    public function feePlans(): BelongsToMany
    {
        return $this->belongsToMany(Fee::class, 'fee_plans')
            ->using(FeePlan::class)
            ->withPivot(['range', 'price']);
    }

    public function coverages(): HasMany
    {
        return $this->hasMany(Coverage::class, 'plan_id', 'id');
    }

    public function businessLine()
    {
        return $this->belongsTo(BusinessLine::class, 'business_line_id', 'id');
    }

    public function businessUnit()
    {
        return $this->hasOne(BusinessUnit::class, 'id', 'business_unit_id');
    }

    public function ageRanges(): HasMany
    {
        return $this->hasMany(AgeRange::class, 'plan_id', 'id');
    }

    public function affiliationCorporates(): BelongsToMany
    {
        return $this->belongsToMany(AffiliationCorporate::class);
    }

    public function condicionado(): HasOne
    {
        return $this->hasOne(PlanCondicionado::class);
    }
}
