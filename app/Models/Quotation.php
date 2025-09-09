<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'description',
        'employee_id',
        'client_id',
        'status_id',
        'labor_fee',
        'delivery_fee',
    ];

    // Relation to client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relation to materials (many-to-many via quotation_materials)
    public function materials()
    {
        return $this->belongsToMany(Material::class, 'quotation_materials')
                    ->withPivot('quantity', 'unit_cost', 'id'); 
    }
}
