<?php

namespace Gopos\Filament\Pages;

use Filament\Pages\Page;
use Gopos\Models\PosSession;
use Gopos\Services\POSSessionService;
use Illuminate\Contracts\Support\Htmlable;

class PosShiftReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'gopos::filament.pages.pos-shift-report';

    public ?PosSession $session = null;

    public array $summary = [];

    public function mount(?int $session = null): void
    {
        if (! $session) {
            abort(404);
        }

        $this->session = PosSession::with(['user', 'transactions', 'transactions.currency'])
            ->findOrFail($session);

        // Ensure user can only see their own sessions (or is admin)
        if ($this->session->user_id !== auth()->id() && ! auth()->user()->hasRole('super_admin')) {
            abort(403);
        }

        $service = app(POSSessionService::class);
        $this->summary = $service->getSessionSummary($this->session);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Shift Report').' - '.($this->session?->opening_time?->format('Y-m-d H:i') ?? '');
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'pos-shift-report';
    }
}
