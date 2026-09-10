<?php

namespace App\Models;

use App\Observers\DepartmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([DepartmentObserver::class])]
class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dept_id',
        'name',
        'code',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function ddos(): HasMany
    {
        return $this->hasMany(Ddo::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
