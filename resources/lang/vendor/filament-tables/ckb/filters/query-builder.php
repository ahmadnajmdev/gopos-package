<?php

return [

    'label' => 'پێکەوەنەری پرسیار',

    'form' => [

        'operator' => [
            'label' => 'ئۆپەرەیتەر',
        ],

        'or_groups' => [

            'label' => 'گرووپەکان',

            'block' => [
                'label' => 'لێکجیاکەرەوە (یان)',
                'or' => 'یان',
            ],

        ],

        'rules' => [

            'label' => 'یاساکان',

            'item' => [
                'and' => 'و',
            ],

        ],

    ],

    'no_rules' => '(هیچ یاسایەک نییە)',

    'item_separators' => [
        'and' => 'و',
        'or' => 'یان',
    ],

    'operators' => [

        'is_filled' => [

            'label' => [
                'direct' => 'پڕکراوەتەوە',
                'inverse' => 'بەتاڵە',
            ],

            'summary' => [
                'direct' => ':attribute پڕکراوەتەوە',
                'inverse' => ':attribute بەتاڵە',
            ],

        ],

        'boolean' => [

            'is_true' => [

                'label' => [
                    'direct' => 'ڕاستە',
                    'inverse' => 'هەڵەیە',
                ],

                'summary' => [
                    'direct' => ':attribute ڕاستە',
                    'inverse' => ':attribute هەڵەیە',
                ],

            ],

        ],

        'date' => [

            'is_after' => [

                'label' => [
                    'direct' => 'لەدوای',
                    'inverse' => 'لەدوای نییە',
                ],

                'summary' => [
                    'direct' => ':attribute لەدوای :dateـە',
                    'inverse' => ':attribute لەدوای :date نییە',
                ],

            ],

            'is_before' => [

                'label' => [
                    'direct' => 'پێش',
                    'inverse' => 'پێش نییە',
                ],

                'summary' => [
                    'direct' => ':attribute پێش :dateـە',
                    'inverse' => ':attribute پێش :date نییە',
                ],

            ],

            'is_date' => [

                'label' => [
                    'direct' => 'بەروارەکەی',
                    'inverse' => 'بەروارەکەی نییە',
                ],

                'summary' => [
                    'direct' => ':attribute بەروارەکەی :dateـە',
                    'inverse' => ':attribute بەروارەکەی :date نییە',
                ],

            ],

            'is_month' => [

                'label' => [
                    'direct' => 'مانگەکەی',
                    'inverse' => 'مانگەکەی نییە',
                ],

                'summary' => [
                    'direct' => ':attribute مانگەکەی :monthـە',
                    'inverse' => ':attribute مانگەکەی :month نییە',
                ],

            ],

            'is_year' => [

                'label' => [
                    'direct' => 'ساڵەکەی',
                    'inverse' => 'ساڵەکەی نییە',
                ],

                'summary' => [
                    'direct' => ':attribute ساڵەکەی :yearـە',
                    'inverse' => ':attribute ساڵەکەی :year نییە',
                ],

            ],

            'form' => [

                'date' => [
                    'label' => 'بەروار',
                ],

                'month' => [
                    'label' => 'مانگ',
                ],

                'year' => [
                    'label' => 'ساڵ',
                ],

            ],

        ],

        'number' => [

            'equals' => [

                'label' => [
                    'direct' => 'یەکسانە بە',
                    'inverse' => 'یەکسان نییە بە',
                ],

                'summary' => [
                    'direct' => ':attribute یەکسانە بە :number',
                    'inverse' => ':attribute یەکسان نییە بە :number',
                ],

            ],

            'is_max' => [

                'label' => [
                    'direct' => 'زۆرترین بڕەکەی',
                    'inverse' => 'زیاترە لە',
                ],

                'summary' => [
                    'direct' => ':attribute زۆرترین بڕەکەی :numberـە',
                    'inverse' => ':attribute لە :number زیاترە',
                ],

            ],

            'is_min' => [

                'label' => [
                    'direct' => 'کەمترین بڕەکەی',
                    'inverse' => 'کەمترە لە',
                ],

                'summary' => [
                    'direct' => ':attribute کەمترین بڕەکەی :numberـە',
                    'inverse' => ':attribute لە :number کەمترە',
                ],

            ],

            'aggregates' => [

                'average' => [
                    'label' => 'تێکڕا',
                    'summary' => 'تێکڕای :attribute',
                ],

                'max' => [
                    'label' => 'زۆرترین',
                    'summary' => 'زۆرترینی :attribute',
                ],

                'min' => [
                    'label' => 'کەمترین',
                    'summary' => 'کەمترینی :attribute',
                ],

                'sum' => [
                    'label' => 'سەرجەم',
                    'summary' => 'سەرجەمی :attribute',
                ],

            ],

            'form' => [

                'aggregate' => [
                    'label' => 'کۆکەرەوە',
                ],

                'number' => [
                    'label' => 'ژمارە',
                ],

            ],

        ],

        'relationship' => [

            'equals' => [

                'label' => [
                    'direct' => 'خاوەنی',
                    'inverse' => 'خاوەنی نییە',
                ],

                'summary' => [
                    'direct' => 'خاوەنی :count لە :relationshipـە',
                    'inverse' => 'خاوەنی :count لە :relationship نییە',
                ],

            ],

            'has_max' => [

                'label' => [
                    'direct' => 'خاوەنی زۆرترین',
                    'inverse' => 'خاوەنی زیاتر لە',
                ],

                'summary' => [
                    'direct' => 'خاوەنی زۆرترینی :count لە :relationshipـە',
                    'inverse' => 'خاوەنی زیاترە لە :count لە :relationship',
                ],

            ],

            'has_min' => [

                'label' => [
                    'direct' => 'خاوەنی کەمترین',
                    'inverse' => 'خاوەنی کەمتر لە',
                ],

                'summary' => [
                    'direct' => 'خاوەنی کەمترینی :count لە :relationshipـە',
                    'inverse' => 'خاوەنی کەمترە لە :count لە :relationship',
                ],

            ],

            'is_empty' => [

                'label' => [
                    'direct' => 'بەتاڵە',
                    'inverse' => 'بەتاڵ نییە',
                ],

                'summary' => [
                    'direct' => ':relationship بەتاڵە',
                    'inverse' => ':relationship بەتاڵ نییە',
                ],

            ],

            'is_related_to' => [

                'label' => [

                    'single' => [
                        'direct' => 'بریتییە لە',
                        'inverse' => 'بریتی نییە لە',
                    ],

                    'multiple' => [
                        'direct' => 'لەخۆدەگرێت',
                        'inverse' => 'لەخۆناگرێت',
                    ],

                ],

                'summary' => [

                    'single' => [
                        'direct' => ':relationship بریتییە لە :values',
                        'inverse' => ':relationship بریتی نییە لە :values',
                    ],

                    'multiple' => [
                        'direct' => ':relationship :values لەخۆدەگرێت',
                        'inverse' => ':relationship :values لەخۆناگرێت',
                    ],

                    'values_glue' => [
                        0 => ', ',
                        'final' => ' یان ',
                    ],

                ],

                'form' => [

                    'value' => [
                        'label' => 'بەها',
                    ],

                    'values' => [
                        'label' => 'بەهاکان',
                    ],

                ],

            ],

            'form' => [

                'count' => [
                    'label' => 'ژمارە',
                ],

            ],

        ],

        'select' => [

            'is' => [

                'label' => [
                    'direct' => 'بریتییە لە',
                    'inverse' => 'بریتی نییە لە',
                ],

                'summary' => [
                    'direct' => ':attribute بریتییە لە :values',
                    'inverse' => ':attribute بریتی نییە لە :values',
                    'values_glue' => [
                        ', ',
                        'final' => ' یان ',
                    ],
                ],

                'form' => [

                    'value' => [
                        'label' => 'بەها',
                    ],

                    'values' => [
                        'label' => 'بەهاکان',
                    ],

                ],

            ],

        ],

        'text' => [

            'contains' => [

                'label' => [
                    'direct' => 'لەخۆدەگرێت',
                    'inverse' => 'لەخۆناگرێت',
                ],

                'summary' => [
                    'direct' => ':attribute دەقی :text لەخۆدەگرێت',
                    'inverse' => ':attribute دەقی :text لەخۆناگرێت',
                ],

            ],

            'ends_with' => [

                'label' => [
                    'direct' => 'بەمە کۆتایی دێت',
                    'inverse' => 'بەمە کۆتایی نایەت',
                ],

                'summary' => [
                    'direct' => ':attribute بە :text کۆتایی دێت',
                    'inverse' => ':attribute بە :text کۆتایی نایەت',
                ],

            ],

            'equals' => [

                'label' => [
                    'direct' => 'یەکسانە بە',
                    'inverse' => 'یەکسان نییە بە',
                ],

                'summary' => [
                    'direct' => ':attribute یەکسانە بە :text',
                    'inverse' => ':attribute یەکسان نییە بە :text',
                ],

            ],

            'starts_with' => [

                'label' => [
                    'direct' => 'بەمە دەست پێدەکات',
                    'inverse' => 'بەمە دەست پێناکات',
                ],

                'summary' => [
                    'direct' => ':attribute بە :text دەست پێدەکات',
                    'inverse' => ':attribute بە :text دەست پێناکات',
                ],

            ],

            'form' => [

                'text' => [
                    'label' => 'دەق',
                ],

            ],

        ],

    ],

    'actions' => [

        'add_rule' => [
            'label' => 'یاسایەک زیاد بکە',
        ],

        'add_rule_group' => [
            'label' => 'گرووپی یاسا زیاد بکە',
        ],

    ],

];
