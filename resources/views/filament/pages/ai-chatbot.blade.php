<x-filament-panels::page>
    <div class="space-y-4">
        <form wire:submit="askQuestion" class="space-y-4">
            {{ $this->form }}

            <x-filament::button type="submit" class="w-full">
                {{ __('Ask Question') }}
            </x-filament::button>
        </form>

        @if($aiResponse)
            <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <div class="prose dark:prose-invert max-w-none">
                    {{ $aiResponse }}
                </div>
            </div>
        @endif

        @if(count($conversationHistory) > 0)
            <div class="mt-8">
                <div class="space-y-4">
                    @foreach($conversationHistory as $conversation)
                        <div class="p-4 bg-white dark:bg-gray-700 rounded-lg shadow">
                            <p class="font-medium text-sm text-gray-500 dark:text-gray-200">
                                {{ \Carbon\Carbon::parse($conversation['created_at'])->diffForHumans() }}
                            </p>
                            <p class="mt-2 font-medium">{{__('Question')}}:</p>
                            <p class="text-gray-600 dark:text-gray-200">{{ $conversation['user_query'] }}</p>
                            <p class="mt-2 font-medium">{{__('Answer')}}:</p>
                            <div class="prose dark:prose-invert max-w-none">
                                {{ $conversation['ai_response'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
