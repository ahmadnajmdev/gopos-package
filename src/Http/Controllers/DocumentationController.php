<?php

namespace Gopos\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class DocumentationController extends Controller
{
    protected string $docsPath;

    protected array $supportedLocales = ['en', 'ar', 'ckb'];

    protected array $rtlLocales = ['ar', 'ckb'];

    protected array $navigation = [
        'user' => [
            'title_key' => 'docs.user_documentation',
            'icon' => 'book-open',
            'items' => [
                'QUICK-START' => 'docs.quick_start',
                'USER-GUIDE' => 'docs.user_guide',
                'FAQ' => 'docs.faq',
            ],
        ],
        'technical' => [
            'title_key' => 'docs.technical_documentation',
            'icon' => 'code-bracket',
            'items' => [
                '01-rbac-roles-permissions' => 'docs.roles_permissions',
                '02-pos-enhancements' => 'docs.pos_features',
                '03-inventory-enhancements' => 'docs.inventory_management',
                '04-accounting-enhancements' => 'docs.accounting_features',
                '05-hr-module' => 'docs.hr_module',
            ],
        ],
    ];

    public function __construct()
    {
        // Use app's docs if they exist (customer override), otherwise use package docs
        $appDocs = base_path('docs');
        $packageDocs = dirname(__DIR__, 3).'/docs';

        $this->docsPath = is_dir($appDocs) ? $appDocs : $packageDocs;
    }

    public function index()
    {
        return redirect()->route('docs.show', 'QUICK-START');
    }

    public function show(Request $request, string $slug)
    {
        // Handle language switching
        if ($request->has('lang')) {
            $lang = $request->get('lang');
            if (in_array($lang, $this->supportedLocales)) {
                Session::put('docs_locale', $lang);
                App::setLocale($lang);
            }

            return redirect()->route('docs.show', $slug);
        }

        // Get current locale
        $locale = Session::get('docs_locale', App::getLocale());
        if (! in_array($locale, $this->supportedLocales)) {
            $locale = 'en';
        }
        App::setLocale($locale);

        // Try to find localized version first, fall back to default
        $filePath = $this->docsPath.'/'.$locale.'/'.$slug.'.md';
        if (! File::exists($filePath)) {
            $filePath = $this->docsPath.'/'.$slug.'.md';
        }

        if (! File::exists($filePath)) {
            abort(404, 'Documentation not found');
        }

        $content = File::get($filePath);
        $html = $this->parseMarkdown($content);
        $title = $this->extractTitle($content);
        $toc = $this->generateTableOfContents($content);

        // Build translated navigation
        $translatedNav = $this->getTranslatedNavigation();

        return view('gopos::docs.show', [
            'html' => $html,
            'title' => $title,
            'toc' => $toc,
            'navigation' => $translatedNav,
            'currentSlug' => $slug,
            'currentLocale' => $locale,
            'supportedLocales' => $this->supportedLocales,
            'isRtl' => in_array($locale, $this->rtlLocales),
            'localeNames' => [
                'en' => 'English',
                'ar' => 'العربية',
                'ckb' => 'کوردی',
            ],
        ]);
    }

    protected function getTranslatedNavigation(): array
    {
        $translated = [];

        foreach ($this->navigation as $key => $section) {
            $translated[$key] = [
                'title' => __($section['title_key']),
                'icon' => $section['icon'],
                'items' => [],
            ];

            foreach ($section['items'] as $slug => $titleKey) {
                $translated[$key]['items'][$slug] = __($titleKey);
            }
        }

        return $translated;
    }

    protected function parseMarkdown(string $markdown): string
    {
        // Convert headers with improved styling
        $html = preg_replace(
            '/^#### (.+)$/m',
            '<h4 class="text-base font-semibold text-gray-800 dark:text-gray-100 mt-6 mb-3 flex items-center gap-2" id="$1"><span class="w-1 h-4 bg-gray-300 dark:bg-gray-600 rounded-full"></span>$1</h4>',
            $markdown
        );
        $html = preg_replace(
            '/^### (.+)$/m',
            '<h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-8 mb-4 flex items-center gap-2" id="$1"><span class="w-1.5 h-5 bg-primary-400 dark:bg-primary-500 rounded-full"></span>$1</h3>',
            $html
        );
        $html = preg_replace(
            '/^## (.+)$/m',
            '<h2 class="text-xl font-bold text-gray-900 dark:text-white mt-12 mb-5 pb-3 border-b-2 border-primary-200 dark:border-primary-800 flex items-center gap-3" id="$1"><span class="w-2 h-6 bg-primary-500 rounded-full"></span>$1</h2>',
            $html
        );
        $html = preg_replace(
            '/^# (.+)$/m',
            '<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 pb-4 border-b-2 border-gray-200 dark:border-gray-700">$1</h1>',
            $html
        );

        // Convert code blocks with better styling and language badge
        $html = preg_replace_callback('/```(\w+)?\n([\s\S]*?)```/', function ($matches) {
            $lang = $matches[1] ?? '';
            $code = htmlspecialchars($matches[2]);
            $langBadge = $lang ? '<span class="absolute top-2 end-2 px-2 py-0.5 text-xs font-medium bg-gray-700 text-gray-300 rounded">'.strtoupper($lang).'</span>' : '';

            return '<div class="relative my-6 group"><div class="absolute -inset-1 bg-gradient-to-r from-primary-500/20 to-purple-500/20 rounded-xl blur opacity-25 group-hover:opacity-50 transition"></div><pre class="relative bg-gray-900 text-gray-100 p-5 rounded-xl overflow-x-auto shadow-lg" dir="ltr">'.$langBadge.'<code class="language-'.$lang.' text-sm leading-relaxed">'.$code.'</code></pre></div>';
        }, $html);

        // Convert inline code with improved styling
        $html = preg_replace(
            '/`([^`]+)`/',
            '<code class="bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-md text-sm font-mono border border-primary-200 dark:border-primary-800" dir="ltr">$1</code>',
            $html
        );

        // Convert tables with better styling
        $html = preg_replace_callback('/\|(.+)\|\n\|[-:\| ]+\|\n((?:\|.+\|\n?)+)/', function ($matches) {
            $headers = array_map('trim', explode('|', trim($matches[1], '|')));
            $rows = array_filter(explode("\n", trim($matches[2])));

            $table = '<div class="overflow-x-auto my-8 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';
            $table .= '<thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-800/80"><tr>';
            foreach ($headers as $header) {
                $table .= '<th class="px-5 py-4 text-start text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">'.trim($header).'</th>';
            }
            $table .= '</tr></thead><tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">';

            $rowIndex = 0;
            foreach ($rows as $row) {
                $cells = array_map('trim', explode('|', trim($row, '|')));
                $bgClass = $rowIndex % 2 === 0 ? '' : 'bg-gray-50/50 dark:bg-gray-800/30';
                $table .= '<tr class="'.$bgClass.' hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-colors">';
                foreach ($cells as $cell) {
                    $table .= '<td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-200">'.trim($cell).'</td>';
                }
                $table .= '</tr>';
                $rowIndex++;
            }
            $table .= '</tbody></table></div>';

            return $table;
        }, $html);

        // Convert bold with better weight
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong class="font-bold text-gray-900 dark:text-white">$1</strong>', $html);

        // Convert italic
        $html = preg_replace('/\*(.+?)\*/', '<em class="italic text-gray-600 dark:text-gray-400">$1</em>', $html);

        // Convert links with better hover effects
        $html = preg_replace(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            '<a href="$2" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 underline decoration-primary-300 dark:decoration-primary-700 underline-offset-2 hover:decoration-primary-500 transition-colors">$1</a>',
            $html
        );

        // Convert unordered lists with better styling
        $html = preg_replace_callback('/(?:^[-*] .+\n?)+/m', function ($matches) {
            $items = preg_split('/^[-*] /m', $matches[0], -1, PREG_SPLIT_NO_EMPTY);
            $list = '<ul class="my-6 space-y-3 ps-2">';
            foreach ($items as $item) {
                $list .= '<li class="flex items-start gap-3 text-gray-700 dark:text-gray-300"><span class="mt-2 w-2 h-2 bg-primary-500 rounded-full shrink-0"></span><span class="leading-relaxed">'.trim($item).'</span></li>';
            }
            $list .= '</ul>';

            return $list;
        }, $html);

        // Convert ordered lists with numbered badges
        $html = preg_replace_callback('/(?:^\d+\. .+\n?)+/m', function ($matches) {
            $items = preg_split('/^\d+\. /m', $matches[0], -1, PREG_SPLIT_NO_EMPTY);
            $list = '<ol class="my-6 space-y-4 ps-2">';
            $num = 1;
            foreach ($items as $item) {
                $list .= '<li class="flex items-start gap-4 text-gray-700 dark:text-gray-300"><span class="flex items-center justify-center w-7 h-7 bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 text-sm font-bold rounded-full shrink-0">'.$num.'</span><span class="leading-relaxed pt-0.5">'.trim($item).'</span></li>';
                $num++;
            }
            $list .= '</ol>';

            return $list;
        }, $html);

        // Convert blockquotes with icon and better styling
        $html = preg_replace(
            '/^> (.+)$/m',
            '<blockquote class="flex gap-4 my-6 p-5 bg-primary-50 dark:bg-primary-900/20 border-s-4 border-primary-500 rounded-e-xl"><svg class="w-6 h-6 text-primary-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg><span class="text-gray-700 dark:text-gray-300 leading-relaxed">$1</span></blockquote>',
            $html
        );

        // Convert horizontal rules with better styling
        $html = preg_replace('/^---+$/m', '<hr class="my-12 border-0 h-px bg-gradient-to-r from-transparent via-gray-300 dark:via-gray-600 to-transparent">', $html);

        // Convert paragraphs (lines that aren't already wrapped)
        $lines = explode("\n", $html);
        $result = [];
        $inParagraph = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                if ($inParagraph) {
                    $result[] = '</p>';
                    $inParagraph = false;
                }
                $result[] = '';
            } elseif (preg_match('/^<(h[1-6]|ul|ol|pre|table|div|blockquote|hr|svg)/', $trimmed)) {
                if ($inParagraph) {
                    $result[] = '</p>';
                    $inParagraph = false;
                }
                $result[] = $line;
            } else {
                if (! $inParagraph && ! preg_match('/^</', $trimmed)) {
                    $result[] = '<p class="text-gray-700 dark:text-gray-300 leading-7 my-5">';
                    $inParagraph = true;
                }
                $result[] = $line;
            }
        }

        if ($inParagraph) {
            $result[] = '</p>';
        }

        return implode("\n", $result);
    }

    protected function extractTitle(string $markdown): string
    {
        if (preg_match('/^# (.+)$/m', $markdown, $matches)) {
            return $matches[1];
        }

        return 'Documentation';
    }

    protected function generateTableOfContents(string $markdown): array
    {
        $toc = [];
        preg_match_all('/^## (.+)$/m', $markdown, $matches);

        foreach ($matches[1] as $heading) {
            $toc[] = [
                'title' => $heading,
                'slug' => Str::slug($heading),
            ];
        }

        return $toc;
    }
}
