<?php

namespace App\Modules\Chatbot\Http\Controllers\Client;

use App\Models\ChatbotFaq;
use App\Models\ChatbotMessage;
use App\Services\ChatbotService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbot,
    ) {}

    public function index(Request $request)
    {
        $userId = $request->user()?->id;

        $messages = ChatbotMessage::when($userId, fn($q) => $q->where('user_id', $userId))
            ->orderBy('created_at')
            ->take(50)
            ->get();

        $faqs = ChatbotFaq::where('is_active', true)->orderBy('sort_order')->get();

        return view('client.chatbot.index', compact('messages', 'faqs'));
    }

    public function message(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $text = trim($validated['message']);

        try {
            // Guardar mensaje del usuario
            ChatbotMessage::create([
                'user_id'    => $user?->id,
                'session_id' => $request->session()->getId(),
                'sender'     => 'user',
                'message'    => $text,
            ]);

            // Generar respuesta con el servicio mejorado
            $reply = $this->chatbot->processMessage($user, $text);

            // Guardar respuesta del bot
            ChatbotMessage::create([
                'user_id'    => $user?->id,
                'session_id' => $request->session()->getId(),
                'sender'     => 'bot',
                'message'    => $reply,
            ]);

            return response()->json(['reply' => $reply]);

        } catch (Throwable $e) {
            report($e);

            Log::error('[ChatbotController] No se pudo procesar el mensaje del chatbot.', [
                'user_id' => $user?->id,
                'session_id' => $request->session()->getId(),
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => 'Tuve un pequeño contratiempo. ¿Puedes intentarlo de nuevo?',
                'error' => true,
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}