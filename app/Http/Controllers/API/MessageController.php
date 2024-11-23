<?php

namespace App\Http\Controllers\API;


use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Http\Resources\MessageResource;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $messages = Message::where('conversation_id', $request->conversation_id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return MessageResource::collection($messages);
    }

    public function store(MessageRequest $request)
    {   
        $user = auth()->user();
        
        $message = $user->messages()->create($request->validated());
        
        event(new NewMessage($message));

        return new MessageResource($message);
    }

    public function show(Message $message)
    {
        return new MessageResource($message);
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return response()->noContent();
    }
}
