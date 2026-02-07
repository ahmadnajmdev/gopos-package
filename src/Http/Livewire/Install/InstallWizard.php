<?php

namespace Gopos\Http\Livewire\Install;

use Gopos\Services\InstallationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.install')]
#[Title('Installation Wizard')]
class InstallWizard extends Component
{
    // Current step (1-6)
    #[Url]
    public int $step = 1;

    // Step 1: Requirements
    public array $requirements = [];

    public bool $requirementsPassed = false;

    // Step 2: Database
    public array $databaseStatus = [];

    public bool $databaseConnected = false;

    // Step 3: Modules
    public array $selectedModules = [];

    public array $availableModules = [];

    // Step 4: Admin User
    public string $adminName = '';

    public string $adminEmail = '';

    public string $adminPassword = '';

    public string $adminPasswordConfirmation = '';

    // Step 5: Business Settings
    public string $businessName = '';

    public ?int $currencyId = null;

    public string $businessAddress = '';

    public string $businessPhone = '';

    public string $businessEmail = '';

    public bool $seedDemoData = false;

    // Step 6: Finalization
    public array $installationLog = [];

    public bool $installationComplete = false;

    public bool $installationFailed = false;

    public bool $isInstalling = false;

    // Language
    public string $locale = 'en';

    protected InstallationService $installService;

    public function boot(InstallationService $installService): void
    {
        $this->installService = $installService;
    }

    public function mount(): void
    {
        $this->locale = session('locale', config('app.locale', 'en'));
        app()->setLocale($this->locale);

        $this->loadStep();
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }

    protected function loadStep(): void
    {
        match ($this->step) {
            1 => $this->loadRequirements(),
            2 => $this->loadDatabaseStatus(),
            3 => $this->loadModules(),
            default => null,
        };
    }

    protected function loadRequirements(): void
    {
        $this->requirements = $this->installService->checkRequirements();
        $this->requirementsPassed = $this->installService->allRequirementsPassed();
    }

    protected function loadDatabaseStatus(): void
    {
        $this->databaseStatus = $this->installService->testDatabaseConnection();
        $this->databaseConnected = $this->databaseStatus['success'] ?? false;
    }

    protected function loadModules(): void
    {
        $this->availableModules = $this->installService->getAvailableModules();

        // Pre-select default modules
        if (empty($this->selectedModules)) {
            foreach ($this->availableModules as $key => $module) {
                if ($module['default']) {
                    $this->selectedModules[] = $key;
                }
            }
        }
    }

    public function getCurrenciesProperty()
    {
        // Return default currencies for selection
        return collect([
            ['id' => 1, 'name' => 'Iraqi Dinar', 'code' => 'IQD', 'symbol' => 'د.ع'],
            ['id' => 2, 'name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$'],
        ]);
    }

    public function nextStep(): void
    {
        // Validate current step before proceeding
        if (! $this->validateCurrentStep()) {
            return;
        }

        $this->step = min($this->step + 1, 6);
        $this->loadStep();
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
        $this->loadStep();
    }

    public function goToStep(int $step): void
    {
        // Only allow going back or to completed steps
        if ($step <= $this->step) {
            $this->step = $step;
            $this->loadStep();
        }
    }

    public function validateCurrentStep(): bool
    {
        return match ($this->step) {
            1 => $this->requirementsPassed,
            2 => $this->databaseConnected,
            3 => ! empty($this->selectedModules),
            4 => $this->validateAdminUser(),
            5 => $this->validateBusinessSettings(),
            default => true,
        };
    }

    protected function validateAdminUser(): bool
    {
        $this->resetErrorBag();

        $this->validate([
            'adminName' => 'required|min:2',
            'adminEmail' => 'required|email',
            'adminPassword' => 'required|min:8',
            'adminPasswordConfirmation' => 'required|same:adminPassword',
        ], [
            'adminName.required' => __('Full name is required'),
            'adminName.min' => __('Name must be at least 2 characters'),
            'adminEmail.required' => __('Email is required'),
            'adminEmail.email' => __('Please enter a valid email'),
            'adminPassword.required' => __('Password is required'),
            'adminPassword.min' => __('Password must be at least 8 characters'),
            'adminPasswordConfirmation.required' => __('Please confirm your password'),
            'adminPasswordConfirmation.same' => __('Passwords do not match'),
        ]);

        return true;
    }

    protected function validateBusinessSettings(): bool
    {
        $this->resetErrorBag();

        $this->validate([
            'businessName' => 'required|min:2',
            'currencyId' => 'required',
        ], [
            'businessName.required' => __('Business name is required'),
            'businessName.min' => __('Business name must be at least 2 characters'),
            'currencyId.required' => __('Please select a currency'),
        ]);

        return true;
    }

    public function refreshRequirements(): void
    {
        $this->loadRequirements();
    }

    public function refreshDatabase(): void
    {
        $this->loadDatabaseStatus();
    }

    public function runInstallation(): void
    {
        $this->installationLog = [];
        $this->installationFailed = false;
        $this->isInstalling = true;

        try {
            // Step 1: Run migrations
            $this->addLog(__('Running database migrations...'));
            $result = $this->installService->runMigrations();
            if (! $result['success']) {
                throw new \Exception(__('Migration failed').': '.($result['message'] ?? __('Unknown error')));
            }
            $this->addLog(__('Migrations completed successfully.'), 'success');

            // Step 2: Seed data
            $this->addLog(__('Seeding initial data...'));
            $result = $this->installService->seedData($this->seedDemoData);
            if (! $result['success']) {
                throw new \Exception(__('Seeding failed').': '.($result['message'] ?? __('Unknown error')));
            }
            $this->addLog(__('Data seeding completed.'), 'success');

            // Step 3: Create admin user
            $this->addLog(__('Creating administrator account...'));
            $result = $this->installService->createAdminUser([
                'name' => $this->adminName,
                'email' => $this->adminEmail,
                'password' => $this->adminPassword,
            ]);
            if (! $result['success']) {
                throw new \Exception(__('Admin creation failed').': '.($result['message'] ?? __('Unknown error')));
            }
            $this->addLog(__('Administrator account created.'), 'success');

            // Step 4: Save business settings
            $this->addLog(__('Saving business settings...'));
            $this->installService->saveBusinessSettings([
                'business_name' => $this->businessName,
                'currency_id' => $this->currencyId,
                'address' => $this->businessAddress,
                'phone' => $this->businessPhone,
                'email' => $this->businessEmail,
            ]);
            $this->addLog(__('Business settings saved.'), 'success');

            // Step 5: Save enabled modules
            $this->addLog(__('Configuring modules...'));
            $this->installService->saveEnabledModules($this->selectedModules);
            $this->addLog(__('Modules configured.'), 'success');

            // Step 6: Clear caches
            $this->addLog(__('Optimizing application...'));
            $this->installService->clearCaches();
            $this->addLog(__('Application optimized.'), 'success');

            // Step 7: Mark as installed
            $this->addLog(__('Finalizing installation...'));
            $this->installService->markAsInstalled();
            $this->addLog(__('Installation completed successfully!'), 'success');

            $this->installationComplete = true;

        } catch (\Exception $e) {
            $this->addLog(__('Error').': '.$e->getMessage(), 'error');
            $this->installationFailed = true;
        }

        $this->isInstalling = false;
    }

    protected function addLog(string $message, string $type = 'info'): void
    {
        $this->installationLog[] = [
            'message' => $message,
            'type' => $type,
            'time' => now()->format('H:i:s'),
        ];
    }

    public function render()
    {
        return view('gopos::livewire.install.install-wizard');
    }
}
