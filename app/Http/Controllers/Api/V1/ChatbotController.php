<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatbotFaq;
use App\Models\ChatbotMessage;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbot,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $messages = ChatbotMessage::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        $faqs = ChatbotFaq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get(['id', 'question', 'answer']);

        return response()->json([
            'messages' => $messages->map(fn (ChatbotMessage $message) => [
                'id' => $message->id,
                'sender' => $message->sender,
                'message' => $message->message,
                'created_at' => $message->created_at?->format('Y-m-d H:i'),
            ]),
            'faqs' => $faqs,
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $text = trim($validated['message']);
        $sessionId = $this->startMobileSession($user->id);

        try {
            ChatbotMessage::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'sender' => 'user',
                'message' => $text,
            ]);

            $reply = $this->chatbot->processMessage($user, $text);

            ChatbotMessage::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'sender' => 'bot',
                'message' => $reply,
            ]);

            session()->save();

            return response()->json(['reply' => $reply]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'reply' => 'Tuve un pequeño contratiempo. ¿Puedes intentarlo de nuevo?',
            ]);
        }
    }

    private function startMobileSession(int $userId): string
    {
        $sessionId = substr(hash('sha1', 'mobile-chat-'.$userId), 0, 40);
        $store = app('session.store');
        $store->setId($sessionId);
        $store->start();

        return $sessionId;
    }
}
