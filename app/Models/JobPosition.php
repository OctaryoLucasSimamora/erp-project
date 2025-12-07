<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'position',
        'department_name',
        'company',
        'job_location',
        'expected_new_employees',
        'job_description'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_name', 'department_name');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'position', 'position');
    }
}