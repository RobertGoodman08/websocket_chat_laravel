<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';
    protected $guarded = false;

    public function readMessages()
    {
        $this->unreadableMessageStatuses()->update([
            'is_read' => true
        ]);
    }

    public function getUsers()
    {
        return $this->users()->get();
    }

    public function getMessagesWithPagination($page): LengthAwarePaginator
    {
        return $this->messages()->with('user')
            ->orderByDesc('created_at')
            ->paginate(5, '*', 'page', $page);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_user', 'chat_id', 'user_id');
    }

    public function chatWith()
    {
        return $this->hasOneThrough(User::class, ChatUser::class, 'chat_id', 'id', 'id', 'user_id')
            ->where('user_id', '!=', auth()->id());
    }

//    public function chatWith()
//    {
//        return $this->users()->wherePivot('user_id', '!=', auth()->id());
//    }


    public function messages()
    {
        return $this->hasMany(Message::class, 'chat_id', 'id');
    }

    public function unreadableMessageStatuses()
    {
        return $this->hasMany(MessageStatus::class, 'chat_id', 'id')
            ->where('user_id', auth()->id())
            ->where('is_read', false);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'chat_id', 'id')
            ->latestOfMany()
            ->with('user');
    }

}
