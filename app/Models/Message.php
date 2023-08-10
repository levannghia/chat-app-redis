<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['room', 'sender', 'content', 'receiver'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender');
    }

    public function receiver() {
        return $this->belongsTo(User::class, 'receiver');
    }

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class, 'room');
    }

    public function reactions(){
        return $this->hasMany(Reaction::class,'msg_id');
    }
}
