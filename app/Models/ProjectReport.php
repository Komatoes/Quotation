<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectReport extends Model
{
    use HasFactory;

    // Table name (optional, Laravel auto-detects this, but good to be explicit)
    protected $table = 'project_reports';

    // Fields that can be mass assigned
    protected $fillable = [
        'quotation_id',
        'progress',
        'report',
    ];

    
    public function quotation(){
      return $this->belongsTo(Quotation::class);}

    


}

