<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function story()
    {
        return $this->belongsTo(Story::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function childComments()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
