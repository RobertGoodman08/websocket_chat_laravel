<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\StoreRequest;
use App\Http\Resources\Chat\ChatResource;
use App\Http\Resources\Message\MessageResource;
use App\Http\Resources\User\UserResource;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::anotherUsers();
        $users = UserResource::collection($users)->resolve();

        $chats = auth()->user()
            ->chats()
            ->has('messages')
            ->with(['lastMessage', 'chatWith'])
            ->withCount('unreadableMessageStatuses')
            ->get();

        $chats = ChatResource::collection($chats)->resolve();
        return inertia('Chat/Index', compact('users', 'chats'));
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $userIds = array_merge($data['users'], [auth()->id()]);
        sort($userIds);

        $userIdsString = implode('-', $userIds);

        try {
            DB::beginTransaction();

            $chat = Chat::updateOrCreate([
                'users' => $userIdsString
            ], [
                'title' => $data['title']
            ]);

            $chat->users()->sync($userIds);

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
        }


        return redirect()->route('chats.show', $chat->id);

    }

    public function show(Chat $chat)
    {
        $page = request('page') ?? 1;

        $users = $chat->getUsers();
        $messages = $chat->getMessagesWithPagination($page);

        $chat->readMessages();

        $isLastPage = $messages->onLastPage();

        $messages = MessageResource::collection($messages)->resolve();

        if ($page > 1) {
            return response()->json([
                'is_last_page' => $isLastPage,
                'messages' => $messages
            ]);
        }

        $users = UserResource::collection($users)->resolve();
        $chat = ChatResource::make($chat)->resolve();

        return inertia('Chat/Show', compact('chat', 'users', 'messages', 'isLastPage'));
    }

}

