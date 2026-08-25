<?php

namespace App\Http\Controllers\Admin;

use App\InternalMessage;
use App\Notifications\InternalMessageReceived;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminInternalMessageController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Internal Messages';
        $this->pageIcon = 'icon-envelope';
    }

    public function index(Request $request)
    {
        $this->teamMembers = User::with('role.role')
            ->whereKeyNot($this->user->id)
            ->orderBy('name')
            ->get();

        $requestedId = (int) $request->query('recipient', 0);
        $this->selectedMember = $this->teamMembers->firstWhere('id', $requestedId)
            ?? $this->teamMembers->first();

        $this->unreadCounts = $this->unreadCounts();
        $this->conversationMessages = collect();

        if ($this->selectedMember) {
            $this->markConversationRead((int) $this->selectedMember->id);
            $this->conversationMessages = $this->conversationQuery((int) $this->selectedMember->id)
                ->latest('id')->limit(100)->get()->reverse()->values();
            $this->unreadCounts = $this->unreadCounts();
        }

        return view('admin.internal-messages.index', $this->data);
    }

    public function conversation(User $recipient): JsonResponse
    {
        abort_if((int) $recipient->id === (int) $this->user->id, 422);

        $this->markConversationRead((int) $recipient->id);
        $messages = $this->conversationQuery((int) $recipient->id)
            ->latest('id')->limit(100)->get()->reverse()->values();

        return response()->json([
            'messages' => $messages->map(fn (InternalMessage $message) => $this->serializeMessage($message)),
            'unread_counts' => $this->unreadCounts(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::notIn([(int) $this->user->id]),
            ],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = InternalMessage::create([
            'sender_id' => $this->user->id,
            'recipient_id' => (int) $validated['recipient_id'],
            'body' => trim($validated['body']),
        ]);
        $message->load('sender:id,name');

        $recipient = User::findOrFail($validated['recipient_id']);
        try {
            $recipient->notify(new InternalMessageReceived($message));
        } catch (\Throwable $exception) {
            Log::warning('Internal message email notification failed.', [
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => $this->serializeMessage($message),
        ]);
    }

    private function conversationQuery(int $otherUserId)
    {
        return InternalMessage::with('sender:id,name')
            ->where(function ($query) use ($otherUserId) {
                $query->where('sender_id', $this->user->id)
                    ->where('recipient_id', $otherUserId);
            })
            ->orWhere(function ($query) use ($otherUserId) {
                $query->where('sender_id', $otherUserId)
                    ->where('recipient_id', $this->user->id);
            });
    }

    private function markConversationRead(int $senderId): void
    {
        InternalMessage::where('sender_id', $senderId)
            ->where('recipient_id', $this->user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function unreadCounts(): array
    {
        return InternalMessage::where('recipient_id', $this->user->id)
            ->whereNull('read_at')
            ->selectRaw('sender_id, COUNT(*) AS total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function serializeMessage(InternalMessage $message): array
    {
        $timezone = $this->global->timezone ?? config('app.timezone');

        return [
            'id' => $message->id,
            'mine' => (int) $message->sender_id === (int) $this->user->id,
            'sender_name' => $message->sender?->name ?? 'Team member',
            'body' => $message->body,
            'time' => $message->created_at->copy()->timezone($timezone)->format('M j, Y g:i A'),
            'read' => $message->read_at !== null,
        ];
    }
}