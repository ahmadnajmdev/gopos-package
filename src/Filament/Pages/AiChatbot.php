<?php

namespace Gopos\Filament\Pages;

use App;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Gopos\Models\ChatConversation;
use Illuminate\Support\Facades\DB;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class AiChatbot extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = -4;

    public function getTitle(): string
    {
        return __('AI Chatbot');
    }

    public static function getNavigationLabel(): string
    {
        return __('AI Chatbot');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('AI');
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public ?string $userQuery = null;

    public ?string $aiResponse = null;

    public array $conversationHistory = [];

    public function mount(): void
    {
        $this->conversationHistory = ChatConversation::where('user_id', auth()->id())
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('clear')
                ->label(__('Clear Messages'))
                ->action(function () {
                    ChatConversation::query()->where('user_id', auth()->id())->delete();
                    $this->conversationHistory = [];
                    $this->userQuery = null;
                    $this->aiResponse = null;
                })
                ->color('danger'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('userQuery')
                    ->label('Ask a question about your data')
                    ->placeholder(__('e.g. What were total sales in March 2025?'))
                    ->required(),
            ]);
    }

    public function askQuestion()
    {
        $this->validate([
            'userQuery' => 'required|string|min:10',
        ]);

        $tables = DB::select('SHOW TABLES');

        $tableNames = array_map(function ($table) {
            return $table->{'Tables_in_'.config('database.connections.mysql.database')};
        }, $tables);
        $tableColumns = [];
        foreach ($tableNames as $tableName) {
            $columns = DB::select("SHOW COLUMNS FROM `$tableName`");
            $columnNames = array_map(function ($column) {
                return $column->Field;
            }, $columns);
            $tableColumns[$tableName] = $columnNames;
        }

        $this->userQuery = trim($this->userQuery);
        try {
            // First, generate SQL query using AI
            $sqlPrompt = "Given these tables and their columns in my MySQL database:\n\n".
                json_encode($tableColumns, JSON_PRETTY_PRINT)."\n\n".
                'Generate a MySQL query to answer this question: '.$this->userQuery.'
                Rules:
                - Profit = Income + Sales - Expenses + Product Costs
                - Income = Sales - Incomes
                - Expenses = Product Costs + Expenses
                - Use the most efficient SQL query possible
                - Use proper JOINs and aggregations as needed
                - Use the correct table and column names
                - add unit suffix to the stock columns
                ';

            $sqlResponse = Prism::text()
                ->using(Provider::Gemini, 'gemini-2.0-flash')->withSystemPrompt('You are an expert SQL developer. Generate only the SQL query without any explanation. The query should be efficient and properly handle JOINs and aggregations as needed.')
                ->withPrompt($sqlPrompt)
                ->asText();

            $generatedSql = $sqlResponse->text;

            // clean up the SQL query from markdown formatting
            $generatedSql = preg_replace('/^```sql\s*/', '', $generatedSql);
            $generatedSql = preg_replace('/\s*```$/', '', $generatedSql);
            $generatedSql = trim($generatedSql);
            // Execute the generated query
            $queryResult = DB::select($generatedSql);

            $language = $this->getCurrentLocale();

            // Generate natural language response
            $responsePrompt = 'Given this user question: '.$this->userQuery."\n\n".
                'And this data from the database: '.json_encode($queryResult)."\n\n".
                "Provide a natural, helpful response that answers the question. Include specific numbers and insights from the data.
                Rules:
                - Use clear and concise language
                - Use Iraqi Dinar (IQD) as the currency
                - Use the most relevant data points
                - Avoid unnecessary jargon or technical terms
                - Don't return decimal places for currency values
                - Always Answer in ".$language.' Language

                ';

            $aiResponse = Prism::text()
                ->using(Provider::Gemini, 'gemini-2.0-flash')
                ->withSystemPrompt('You are a helpful business analyst. Provide clear, concise answers with specific numbers and insights from the data.')
                ->withPrompt($responsePrompt)
                ->asText();

            $this->aiResponse = $aiResponse->text;

            // Save the conversation
            ChatConversation::create([
                'user_id' => auth()->id(),
                'user_query' => $this->userQuery,
                'generated_sql' => $generatedSql,
                'ai_response' => $this->aiResponse,
            ]);

            // Update conversation history
            $this->conversationHistory = ChatConversation::query()->where('user_id', auth()->id())
                ->latest()
                ->limit(5)
                ->get()
                ->toArray();

            // Clear the user query
            $this->userQuery = null;

        } catch (Exception $e) {
            $this->aiResponse = 'I apologize, but I encountered an error processing your question. Please try rephrasing it or contact support if the issue persists.';
        }
    }

    private function getCurrentLocale()
    {
        $code = App::getLocale();

        return match ($code) {
            'ar' => 'Arabic',
            'en' => 'English',
            'ckb' => 'Kurdish Sorani',
            default => 'Kurdish Sorani',
        };
    }

    protected string $view = 'gopos::filament.pages.ai-chatbot';
}
