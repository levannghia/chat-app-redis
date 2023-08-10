<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;
    protected $table = 'chatrooms';
    protected $fillable = ['name', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class,'user_room', 'room_id');
    }
}
