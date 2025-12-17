<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'type',
        'configuration',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $casts = [
        'configuration' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->belongsToMany(Report::class, 'report_data_source');
    }
}