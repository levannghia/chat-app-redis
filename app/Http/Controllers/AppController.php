<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatRoom;
use Illuminate\Support\Facades\Auth;
use App\Models\Emoji;

class AppController extends Controller
{
    public function index()
    {
        $data = ['user' => Auth::user(), 'emojis' => Emoji::all(), 'rooms' => Chatroom::all()];
        return view('app', ['data' => $data]);
    }
}
