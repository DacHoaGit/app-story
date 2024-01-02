<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function stories(){
        return $this->hasMany(Story::class);
    }
}
