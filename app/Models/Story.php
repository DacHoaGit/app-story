<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function storyType()
    {
        return $this->belongsTo(StoryType::class);
    }

    public function chapters(){
        return $this->hasMany(Chapter::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }
}
