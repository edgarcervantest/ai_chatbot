<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="max-w-3xl mx-auto h-full flex flex-col p-4">
    <!-- {{-- HEADER --}} -->

    <div class = "mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Chatbot</h1>
         
        <p class="text-sm text-gray-500">Chatea con nuestro asistente de IA para obtener ayuda con tus preguntas.</p>
</div>

<!-- {{ --Contendor de Mensajes--}} -->

<div class = "flex-1 overflow-y-auto mb-4 space-y-4">
<!-- {{ --Mensajes--}} -->
    @foreach ($messages as $message)
    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
        <div class="max-w-[80%] rounded-lg px-4 py-2 {{ $message['role'] === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
            @if($message['role'] === 'bot')

            <div class="text-sm whitespace-pre-wrap">
                {!! $message['content'] !!}
            </div>

            @else
                <p class="text-sm whitespace-pre-wrap"> {{ $message['content'] }}</p>
            @endif
        </div>
    </div>
@endforeach


<!-- {{--Estado de Procesamiento--}} -->
 @if($isProcessing)

    <div class="flex justify-start">

        <div class="max-w-[80%] rounded-lg px-4 py-2 bg-gray-200 text-gray-800">
            
            <div class="text-sm whitespace-pre-wrap " wire:stream="response" >
                <!-- stream goes here -->
            </div>
        </div>
    </div>

    <div class="flex justify-start">
        <div class="bg-gray-100 rounded-lg px-4 py-2">

            <div class="flex space-x-1">
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- {{ --Formulario de Envio--}} -->
  <div class="border-t p-4">
    <form wire:submit="sendMessage" class="flex space-x-2">
        <input 
        type="text" 
        
        wire:model="userMessage" 
        
        placeholder="Escribe tu mensaje..." 
        
        class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        
        {{ $isProcessing ? 'disabled' : '' }}

        >

        <button 
        
        type="submit" 
        
        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600"
        
        {{ $isProcessing ? 'disabled' : '' }}
        
        >
            Enviar
    </button>

    </form>

    </div>
</div>