<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'lawyer_id',
        'created_by',
        'title',
        'case_number',
        'court_location',
        'description',
        'notes',
        'hearing_date',
        'hearing_end_date',
        'status'
    ];

    protected $casts = [
        'hearing_date' => 'datetime',
        'hearing_end_date' => 'datetime',
    ];

    /* ---------------- RELATIONSHIPS ---------------- */

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
