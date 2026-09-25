<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningLetter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'letter_number',
        'warning_level',
        'reason',
        'effective_date',
        'expiry_date',
        'attachment_path',
        'issued_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    /**
     * Get the employee who received the warning letter.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the employee who issued the warning letter.
     */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'issued_by');
    }
}
