<?php

namespace Gopos\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeReportPage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:report-page {name} {--service=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Filament report page that extends BaseReportPage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $serviceName = $this->option('service') ?? $name;

        // Ensure the name ends with ReportPage
        if (! Str::endsWith($name, 'ReportPage')) {
            $name .= 'ReportPage';
        }

        // Ensure the service name ends with Report
        if (! Str::endsWith($serviceName, 'Report')) {
            $serviceName .= 'Report';
        }

        $className = Str::studly($name);
        $serviceClassName = Str::studly($serviceName);

        // Define the file path
        $filePath = app_path("Filament/Clusters/Reports/Pages/{$className}.php");

        // Check if the file already exists
        if (File::exists($filePath)) {
            $this->error("Report page {$className} already exists!");

            return 1;
        }

        // Check if the report service exists
        $serviceFilePath = app_path("Services/Reports/{$serviceClassName}.php");
        if (! File::exists($serviceFilePath)) {
            $createService = $this->confirm("Report service {$serviceClassName} does not exist. Would you like to create it?");

            if ($createService) {
                $this->createReportService($serviceClassName);
                $this->info("Report service {$serviceClassName} created successfully!");
            } else {
                $this->error('Cannot create report page without a report service.');

                return 1;
            }
        }

        // Get the navigation sort order
        $navigationSort = $this->ask('Navigation sort order', $this->getNextNavigationSort());

        // Create the report page content
        $content = $this->getReportPageStub($className, $serviceClassName, $navigationSort);

        // Write the file
        File::put($filePath, $content);

        $this->info("Report page {$className} created successfully!");
        $this->info("Location: {$filePath}");
        $this->line('');
        $this->line('The report page will automatically appear in the Reports cluster.');
        $this->line("You can customize the report service at: {$serviceFilePath}");

        return 0;
    }

    protected function getReportPageStub(string $className, string $serviceClassName, int $navigationSort): string
    {
        return <<<PHP
<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use Gopos\Services\Reports\\{$serviceClassName};

class {$className} extends BaseReportPage
{
    protected static ?int \$navigationSort = {$navigationSort};

    protected function getReportClass(): string
    {
        return {$serviceClassName}::class;
    }
}

PHP;
    }

    protected function createReportService(string $serviceClassName): void
    {
        $servicePath = app_path("Services/Reports/{$serviceClassName}.php");

        $title = Str::headline(str_replace('Report', '', $serviceClassName));

        $content = <<<PHP
<?php

namespace Gopos\Services\Reports;

use Illuminate\Support\Collection;

class {$serviceClassName} extends BaseReport
{
    protected string \$title = '{$title}';
    protected bool \$showTotals = false;

    protected array \$columns = [
        // Define your columns here
        // Example:
        // 'column_name' => ['label' => 'Column Label', 'type' => 'text'],
    ];

    protected array \$totalColumns = [];

    public function getData(string \$startDate, string \$endDate): Collection
    {
        // Implement your data fetching logic here
        return collect([]);
    }
}

PHP;

        File::put($servicePath, $content);
    }

    protected function getNextNavigationSort(): int
    {
        $pagesPath = app_path('Filament/Clusters/Reports/Pages');

        if (! File::exists($pagesPath)) {
            return 1;
        }

        $files = File::files($pagesPath);
        $maxSort = 0;

        foreach ($files as $file) {
            if ($file->getFilename() === 'BaseReportPage.php') {
                continue;
            }

            $content = File::get($file->getPathname());

            if (preg_match('/protected static \?int \$navigationSort = (\d+);/', $content, $matches)) {
                $maxSort = max($maxSort, (int) $matches[1]);
            }
        }

        return $maxSort + 1;
    }
}
