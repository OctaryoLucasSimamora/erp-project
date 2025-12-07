<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department_name',
        'position',
        'company',
        'manager',
        'telephone',
        'email',
        'work_location'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_name', 'department_name');
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'position', 'position');
    }
}