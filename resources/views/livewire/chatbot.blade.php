<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Streaming\Events\TextDeltaEvent;
use Illuminate\Support\Facades\Log;

new class extends Component {
    // Propiedad para el mensaje actual del usuario en el input
    public $userMessage = '';
    
    // Propiedad para almacenar el prompt enviado antes de limpiar el input
    public $userPrompt = '';
    
    // Array que almacena todo el historial de mensajes (usuario y bot)
    public $messages = [];
    
    // Acumula la respuesta del bot durante el streaming
    public $streamingResponse = '';
    
    // Indica si el bot está procesando una respuesta
    public $isProcessing = false;

    /**
     * Envía el mensaje del usuario e inicia el proceso de respuesta del bot.
     */
    public function sendMessage()
    {
        if (empty(trim($this->userMessage))) {
            return;
        }

        $this->userPrompt = $this->userMessage;
        $this->userMessage = '';

        $this->messages[] = [
            'role' => 'user',
            'content' => $this->userPrompt
        ];

        $this->isProcessing = true;

        $this->dispatch('mustAskChatbot');
    }

    /**
     * Escucha el evento 'mustAskChatbot' y realiza la petición al proveedor de IA.
     */
    #[On('mustAskChatbot')]
    public function talkToChatbot()
    {
        try {
            $stream = Prism::text()
                ->using(Provider::OpenRouter, 'google/gemma-3-4b-it:free')
                ->withPrompt($this->userPrompt)
                ->asStream();

            foreach ($stream as $chunk) {
                if ($chunk instanceof TextDeltaEvent) {
                    $this->streamingResponse .= $chunk->delta;
                    $this->stream('response', $chunk->delta);
                }
            }
        } catch (\Exception $e) {
            $this->streamingResponse = "Error en la conexión.";
        }

        $this->messages[] = [
            'role' => 'bot',
            'content' => $this->streamingResponse
        ];

        $this->isProcessing = false;
        $this->streamingResponse = '';
        $this->userPrompt = '';
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        return view('livewire.chatbot');
    }
};
?>

<div class="max-w-1xl mx-auto w-full h-[100dvh] flex flex-col p-4 overflow-hidden bg-white dark:bg-zinc-900 rounded-2xl">

    <div class="mb-4 flex-none">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Chatbot IA</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Powered by OpenRouter</p>
    </div>

    <div class="flex-1 overflow-y-auto pr-2 space-y-4 mb-2 scrollbar-thin">
        @foreach ($messages as $message)
            <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div
                    class="max-w-[85%] rounded-2xl px-4 py-2 {{ $message['role'] === 'user' ? 'bg-black text-white shadow-sm' : 'bg-gray-100 text-gray-800 dark:bg-zinc-800 dark:text-zinc-200' }}">
                    @if($message['role'] === 'bot')
                        <div class="text-sm whitespace-pre-wrap leading-relaxed">
                            {!! $message['content'] !!}
                        </div>
                    @else
                        <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ $message['content'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach

        @if($isProcessing)
            <div class="flex justify-start">
                <div class="bg-gray-100 dark:bg-zinc-800 rounded-2xl px-4 py-3">
                    <div class="flex space-x-1" wire:stream="response">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="border-t dark:border-zinc-800 pt-4 pb-6 bg-white dark:bg-zinc-900 flex-none">
        <form wire:submit="sendMessage" class="flex space-x-2">
            <input type="text" wire:model="userMessage" placeholder="Escribe tu mensaje..."
                class="flex-1 border dark:border-zinc-700 dark:bg-zinc-800 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                @disabled($isProcessing)>

            <button type="submit"
                class="bg-black dark:bg-white dark:text-black text-white px-5 py-2 rounded-xl hover:opacity-90 disabled:opacity-50 font-medium transition-all"
                @disabled($isProcessing)>
                Enviar
            </button>
        </form>
        <p class="text-[10px] text-center text-gray-400 mt-2">IA puede cometer errores. Verifica la información.</p>
    </div>
</div>