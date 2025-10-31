<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotationstatus extends Model
{
    use HasFactory;

    // Tell Laravel the exact table name
    protected $table = 'quotation_status';

    protected $fillable = [
        'status_name',
    ];

    // One status can belong to many quotations
    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'status_id');
    }
}
