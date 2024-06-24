<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Topic;

class Post extends Model
{
    use HasFactory;
    protected $table = 'db_post';
    protected $with = ['topic'];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

}