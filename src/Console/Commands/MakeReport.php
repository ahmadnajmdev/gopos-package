<?php

namespace Gopos\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeReport extends Command
{
    protected $signature = 'make:report {name} {--model=} {--columns=}';

    protected $description = 'Create a new report class';

    public function handle()
    {
        $name = $this->argument('name');
        $className = Str::studly($name).'Report';
        $model = $this->option('model');
        $columns = $this->option('columns');

        $stub = $this->getStub($model, $columns);
        $stub = $this->replacePlaceholders($stub, $className, $model);

        $path = app_path("Services/Reports/{$className}.php");

        if (file_exists($path)) {
            $this->error("Report {$className} already exists!");

            return 1;
        }

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, $stub);

        $this->info("Report created successfully: {$className}");
        $this->info("Don't forget to register it in the Reports page:");
        $this->line('protected array $availableReports = [');
        $this->line("    '".Str::snake($name)."' => {$className}::class,");
        $this->line('];');

        return 0;
    }

    protected function getStub(?string $model, ?string $columns): string
    {
        if ($model && $columns) {
            return $this->getModelStub();
        }

        return $this->getBasicStub();
    }

    protected function getBasicStub(): string
    {
        return <<<'STUB'
<?php

namespace Gopos\Services\Reports;

use Illuminate\Support\Collection;

class {{className}} extends BaseReport
{
    protected string $title = '{{title}} Report';
    protected bool $showTotals = true;

    protected array $columns = [
        // Define your columns here
        // 'column_key' => ['label' => 'Column Label', 'type' => 'text|currency|number|date'],
    ];

    protected array $totalColumns = [
        // List columns to sum in totals row
    ];

    public function getData(string $startDate, string $endDate): Collection|array
    {
        // Implement your data fetching logic here
        return collect([]);
    }
}
STUB;
    }

    protected function getModelStub(): string
    {
        return <<<'STUB'
<?php

namespace Gopos\Services\Reports;

use Gopos\Models\{{model}};
use Illuminate\Support\Collection;

class {{className}} extends BaseReport
{
    protected string $title = '{{title}} Report';
    protected bool $showTotals = true;

    protected array $columns = [
        {{columns}}
    ];

    protected array $totalColumns = [{{totalColumns}}];

    public function getData(string $startDate, string $endDate): Collection
    {
        return {{model}}::query()
            ->whereBetween('{{dateColumn}}', [$startDate, $endDate])
            ->with([{{relations}}])
            ->get()
            ->map(function ($item) {
                return [
                    {{mappedColumns}}
                ];
            });
    }
}
STUB;
    }

    protected function replacePlaceholders(string $stub, string $className, ?string $model): string
    {
        $title = Str::title(str_replace('Report', '', $className));

        $stub = str_replace('{{className}}', $className, $stub);
        $stub = str_replace('{{title}}', $title, $stub);

        if ($model) {
            $modelName = Str::studly($model);
            $stub = str_replace('{{model}}', $modelName, $stub);

            // Parse columns option
            $columnsOption = $this->option('columns');
            if ($columnsOption) {
                $columnsList = explode(',', $columnsOption);
                $columns = [];
                $totalColumns = [];
                $mappedColumns = [];

                foreach ($columnsList as $col) {
                    $col = trim($col);
                    $parts = explode(':', $col);
                    $columnName = $parts[0];
                    $columnType = $parts[1] ?? 'text';

                    $label = Str::title(str_replace('_', ' ', $columnName));
                    $columns[] = "'{$columnName}' => ['label' => '{$label}', 'type' => '{$columnType}']";

                    if (in_array($columnType, ['currency', 'number'])) {
                        $totalColumns[] = "'{$columnName}'";
                    }

                    $mappedColumns[] = "'{$columnName}' => \$item->{$columnName}";
                }

                $stub = str_replace('{{columns}}', implode(",\n        ", $columns), $stub);
                $stub = str_replace('{{totalColumns}}', implode(', ', $totalColumns), $stub);
                $stub = str_replace('{{mappedColumns}}', implode(",\n                    ", $mappedColumns), $stub);

                // Simple defaults for date and relations
                $stub = str_replace('{{dateColumn}}', 'created_at', $stub);
                $stub = str_replace('{{relations}}', '', $stub);
            }
        }

        return $stub;
    }
}
