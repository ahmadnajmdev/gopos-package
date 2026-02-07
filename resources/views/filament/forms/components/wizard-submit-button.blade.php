<x-filament::button
    type="submit"
    form="form"
    size="lg"
    wire:loading.attr="disabled"
>
    <x-filament::loading-indicator wire:loading wire:target="create" class="h-5 w-5" />

    <span wire:loading.remove wire:target="create">
        {{ __('team.register_company') }}
    </span>

    <span wire:loading wire:target="create">
        {{ __('team.register_company') }}...
    </span>
</x-filament::button>
