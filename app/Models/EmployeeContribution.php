<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'fin_year',
        'contribution_amount',
        'contribution_date',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'contribution_amount' => 'float',
            'contribution_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
