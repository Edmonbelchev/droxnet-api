<?php

namespace App\Http\Controllers\API;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\ConversationCollection;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $request->input('query');
        $perPage = $request->input('per_page', 15);


        if ($user->role === 'employer') {
            $response = $user->conversations()
                ->whereHas('freelancer', function ($q) use ($query) {
                $q->where('first_name', 'like', "%$query%")
                  ->orWhere('last_name', 'like', "%$query%");
            })
                ->latest()
                ->paginate($perPage);
        } else {
            $response = $user->conversations()
                ->whereHas('employer', function ($q) use ($query) {
                    $q->where('first_name', 'like', "%$query%")
                        ->orWhere('last_name', 'like', "%$query%");
                })
                ->latest()
                ->paginate($perPage);
        }

        return new ConversationCollection($response);
    }

    public function show(Conversation $conversation)
    {
        return new ConversationResource($conversation);
    }

    public function destroy(Conversation $conversation)
    {
        $conversation->delete();

        return new StatusResource(true);
    }
}
