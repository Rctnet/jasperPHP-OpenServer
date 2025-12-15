<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'directory_path',
        'main_jrxml_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dataSources()
    {
        return $this->belongsToMany(DataSource::class, 'report_data_source');
    }
}