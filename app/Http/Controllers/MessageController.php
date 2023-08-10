<?php

namespace App\Http\Controllers;

use App\Events\MessagePosted;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $message = Message::with(['sender', 'receiver', 'reactions.user'])->where('room', $request->query('room',''))
        ->latest()
        ->paginate(20);
        return $message;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $message = new Message();
        $message->sender = auth()->id();
        $message->content = $request->input('content', '');
        if ($request->has('receiver') && $request->input('receiver')) {
            $receiver = (int) $request->input('receiver');
            $message->receiver = $receiver;
            $message->room = $message->sender < $receiver ? $message->sender.'__'.$receiver : $receiver.'__'.$message->sender;
        } else {
            $message->room = $request->input('room');
        }
        $message->save();
        broadcast(new MessagePosted($message->load(['sender','reactions.user'])))->toOthers();
        return response()->json(['message' => $message->load(['sender', 'reactions.user'])]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
