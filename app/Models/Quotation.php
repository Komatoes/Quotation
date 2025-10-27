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
        'latest_progress',
        'public_token',
    ];

    // Relation to client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relation to employee (user who created it)
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // Relation to status
    public function status()
    {
        return $this->belongsTo(Quotationstatus::class, 'status_id');
    }

    // Relation to materials (many-to-many via quotation_materials)
    public function materials()
    {
        return $this->belongsToMany(Material::class, 'quotation_materials')
                    ->withPivot('quantity', 'unit_cost', 'id'); 
    }

    public function progressReports()
    {
        return $this->hasMany(ProjectReport::class);
    }

    public function revisions()
    {
        return $this->hasMany(QuotationRevision::class);
    }
}
