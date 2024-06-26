<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Order extends Model
{
    use HasFactory;
    protected $table = 'db_order';
    protected $with = ['user'];
    public $timestamps = false;
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
