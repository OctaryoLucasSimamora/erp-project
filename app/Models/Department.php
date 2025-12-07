<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_name',
        'company'
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_name', 'department_name');
    }

    public function jobPositions()
    {
        return $this->hasMany(JobPosition::class, 'department_name', 'department_name');
    }
}