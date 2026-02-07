<?php

// lang/ku/validation.php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines - Sorani Kurdish
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    | Translated by: [Your Name/Organization - Optional]
    |--------------------------------------------------------------------------
    */

    'accepted' => 'پێویستە خانەی :attribute پەسەند بکرێت.',
    'accepted_if' => 'پێویستە خانەی :attribute پەسەند بکرێت کاتێک :other یەکسانە بە :value.',
    'active_url' => 'پێویستە خانەی :attribute URLێکی دروست بێت.',
    'after' => 'پێویستە خانەی :attribute بەروارێک بێت دوای :date.',
    'after_or_equal' => 'پێویستە خانەی :attribute بەروارێک بێت دوای یاخود یەکسان بە :date.',
    'alpha' => 'پێویستە خانەی :attribute تەنها پیت لەخۆبگرێت.',
    'alpha_dash' => 'پێویستە خانەی :attribute تەنها پیت، ژمارە، داش (-)، و هێڵی ژێرەوە (_) لەخۆبگرێت.',
    'alpha_num' => 'پێویستە خانەی :attribute تەنها پیت و ژمارە لەخۆبگرێت.',
    'array' => 'پێویستە خانەی :attribute ڕیزبەندییەک بێت.',
    'ascii' => 'پێویستە خانەی :attribute تەنها پیت و ژمارەی تاک-بایت و هێماکان لەخۆبگرێت.',
    'before' => 'پێویستە خانەی :attribute بەروارێک بێت پێش :date.',
    'before_or_equal' => 'پێویستە خانەی :attribute بەروارێک بێت پێش یاخود یەکسان بە :date.',
    'between' => [
        'array' => 'پێویستە خانەی :attribute لە نێوان :min و :max دانە بێت.',
        'file' => 'پێویستە خانەی :attribute لە نێوان :min و :max کیلۆبایت بێت.',
        'numeric' => 'پێویستە خانەی :attribute لە نێوان :min و :max بێت.',
        'string' => 'پێویستە خانەی :attribute لە نێوان :min و :max پیت بێت.',
    ],
    'boolean' => 'پێویستە خانەی :attribute ڕاست یان هەڵە بێت.',
    'can' => 'خانەی :attribute بەهایەکی ڕێگەپێنەدراوی تێدایە.',
    'confirmed' => 'پشتڕاستکردنەوەی خانەی :attribute ناگونجێت.',
    'contains' => 'خانەی :attribute بەهایەکی پێویستی تێدا نییە.',
    'current_password' => 'وشەی نهێنی هەڵەیە.',
    'date' => 'پێویستە خانەی :attribute بەروارێکی دروست بێت.',
    'date_equals' => 'پێویستە خانەی :attribute بەروارێک بێت یەکسان بە :date.',
    'date_format' => 'پێویستە خانەی :attribute لەگەڵ فۆرماتی :format بگونجێت.',
    'decimal' => 'پێویستە خانەی :attribute :decimal ژمارەی دەیی هەبێت.',
    'declined' => 'پێویستە خانەی :attribute ڕەتبکرێتەوە.',
    'declined_if' => 'پێویستە خانەی :attribute ڕەتبکرێتەوە کاتێک :other یەکسانە بە :value.',
    'different' => 'پێویستە خانەی :attribute و :other جیاواز بن.',
    'digits' => 'پێویستە خانەی :attribute :digits ڕەنووس بێت.',
    'digits_between' => 'پێویستە خانەی :attribute لە نێوان :min و :max ڕەنووس بێت.',
    'dimensions' => 'خانەی :attribute ڕەهەندی وێنەی نادروستی هەیە.',
    'distinct' => 'خانەی :attribute بەهایەکی دووبارەی هەیە.',
    'doesnt_end_with' => 'پێویستە خانەی :attribute بە یەکێک لەمانە کۆتایی نەیەت: :values.',
    'doesnt_start_with' => 'پێویستە خانەی :attribute بە یەکێک لەمانە دەست پێنەکات: :values.',
    'email' => 'پێویستە خانەی :attribute ناونیشانێکی ئیمەیڵی دروست بێت.',
    'ends_with' => 'پێویستە خانەی :attribute بە یەکێک لەمانە کۆتایی بێت: :values.',
    'enum' => ':attribute ـی هەڵبژێردراو نادروستە.',
    'exists' => ':attribute ـی هەڵبژێردراو نادروستە.', // Or: 'بەهای هەڵبژێردراو بۆ :attribute نادروستە.'
    'extensions' => 'پێویستە خانەی :attribute یەکێک لەم پاشگرانەی هەبێت: :values.',
    'file' => 'پێویستە خانەی :attribute فایلێک بێت.',
    'filled' => 'پێویستە خانەی :attribute بەهایەکی هەبێت.',
    'gt' => [
        'array' => 'پێویستە خانەی :attribute زیاتر لە :value دانە بێت.',
        'file' => 'پێویستە خانەی :attribute گەورەتر بێت لە :value کیلۆبایت.',
        'numeric' => 'پێویستە خانەی :attribute گەورەتر بێت لە :value.',
        'string' => 'پێویستە خانەی :attribute درێژتر بێت لە :value پیت.',
    ],
    'gte' => [
        'array' => 'پێویستە خانەی :attribute :value دانە یان زیاتر بێت.',
        'file' => 'پێویستە خانەی :attribute گەورەتر یان یەکسان بێت بە :value کیلۆبایت.',
        'numeric' => 'پێویستە خانەی :attribute گەورەتر یان یەکسان بێت بە :value.',
        'string' => 'پێویستە خانەی :attribute درێژتر یان یەکسان بێت بە :value پیت.',
    ],
    'hex_color' => 'پێویستە خانەی :attribute ڕەنگێکی شەشپاڵووی دروست بێت.',
    'image' => 'پێویستە خانەی :attribute وێنەیەک بێت.',
    'in' => ':attribute ـی هەڵبژێردراو نادروستە.',
    'in_array' => 'پێویستە خانەی :attribute لەناو :other دا هەبێت.',
    'integer' => 'پێویستە خانەی :attribute ژمارەیەکی تەواو بێت.',
    'ip' => 'پێویستە خانەی :attribute ناونیشانێکی IP دروست بێت.',
    'ipv4' => 'پێویستە خانەی :attribute ناونیشانێکی IPv4 دروست بێت.',
    'ipv6' => 'پێویستە خانەی :attribute ناونیشانێکی IPv6 دروست بێت.',
    'json' => 'پێویستە خانەی :attribute ڕستەیەکی JSON دروست بێت.',
    'list' => 'پێویستە خانەی :attribute لیستێک بێت.',
    'lowercase' => 'پێویستە خانەی :attribute بە پیتی بچووک بێت.',
    'lt' => [
        'array' => 'پێویستە خانەی :attribute کەمتر لە :value دانە بێت.',
        'file' => 'پێویستە خانەی :attribute بچووکتر بێت لە :value کیلۆبایت.',
        'numeric' => 'پێویستە خانەی :attribute بچووکتر بێت لە :value.',
        'string' => 'پێویستە خانەی :attribute کورتتر بێت لە :value پیت.',
    ],
    'lte' => [
        'array' => 'پێویستە خانەی :attribute :value دانە یان کەمتر بێت.',
        'file' => 'پێویستە خانەی :attribute بچووکتر یان یەکسان بێت بە :value کیلۆبایت.',
        'numeric' => 'پێویستە خانەی :attribute بچووکتر یان یەکسان بێت بە :value.',
        'string' => 'پێویستە خانەی :attribute کورتتر یان یەکسان بێت بە :value پیت.',
    ],
    'mac_address' => 'پێویستە خانەی :attribute ناونیشانێکی MAC دروست بێت.',
    'max' => [
        'array' => 'پێویستە خانەی :attribute لە :max دانە زیاتر نەبێت.',
        'file' => 'پێویستە خانەی :attribute گەورەتر نەبێت لە :max کیلۆبایت.',
        'numeric' => 'پێویستە خانەی :attribute گەورەتر نەبێت لە :max.',
        'string' => 'پێویستە خانەی :attribute درێژتر نەبێت لە :max پیت.',
    ],
    'max_digits' => 'پێویستە خانەی :attribute لە :max ڕەنووس زیاتر نەبێت.',
    'mimes' => 'پێویستە خانەی :attribute فایلێک بێت لە جۆری: :values.',
    'mimetypes' => 'پێویستە خانەی :attribute فایلێک بێت لە جۆری: :values.',
    'min' => [
        'array' => 'پێویستە خانەی :attribute لانیکەم :min دانە بێت.',
        'file' => 'پێویستە خانەی :attribute لانیکەم :min کیلۆبایت بێت.',
        'numeric' => 'پێویستە خانەی :attribute لانیکەم :min بێت.',
        'string' => 'پێویستە خانەی :attribute لانیکەم :min پیت بێت.',
    ],
    'min_digits' => 'پێویستە خانەی :attribute لانیکەم :min ڕەنووس بێت.',
    'missing' => 'پێویستە خانەی :attribute ون بێت.',
    'missing_if' => 'پێویستە خانەی :attribute ون بێت کاتێک :other یەکسانە بە :value.',
    'missing_unless' => 'پێویستە خانەی :attribute ون بێت مەگەر :other یەکسان بێت بە :value.',
    'missing_with' => 'پێویستە خانەی :attribute ون بێت کاتێک :values ئامادەیە.',
    'missing_with_all' => 'پێویستە خانەی :attribute ون بێت کاتێک :values ئامادەن.',
    'multiple_of' => 'پێویستە خانەی :attribute چەندجارەی :value بێت.',
    'not_in' => ':attribute ـی هەڵبژێردراو نادروستە.',
    'not_regex' => 'فۆرماتی خانەی :attribute نادروستە.',
    'numeric' => 'پێویستە خانەی :attribute ژمارەیەک بێت.',
    'password' => [
        'letters' => 'پێویستە خانەی :attribute لانیکەم یەک پیتی تێدابێت.',
        'mixed' => 'پێویستە خانەی :attribute لانیکەم یەک پیتی گەورە و یەک پیتی بچووکی تێدابێت.',
        'numbers' => 'پێویستە خانەی :attribute لانیکەم یەک ژمارەی تێدابێت.',
        'symbols' => 'پێویستە خانەی :attribute لانیکەم یەک هێمای تێدابێت.',
        'uncompromised' => ':attribute ـی دراو لە دزەپێکردنی داتادا دەرکەوتووە. تکایە :attribute ـێکی جیاواز هەڵبژێرە.',
    ],
    'present' => 'پێویستە خانەی :attribute ئامادە بێت.',
    'present_if' => 'پێویستە خانەی :attribute ئامادە بێت کاتێک :other یەکسانە بە :value.',
    'present_unless' => 'پێویستە خانەی :attribute ئامادە بێت مەگەر :other یەکسان بێت بە :value.',
    'present_with' => 'پێویستە خانەی :attribute ئامادە بێت کاتێک :values ئامادەیە.',
    'present_with_all' => 'پێویستە خانەی :attribute ئامادە بێت کاتێک :values ئامادەن.',
    'prohibited' => 'خانەی :attribute قەدەغەکراوە.',
    'prohibited_if' => 'خانەی :attribute قەدەغەکراوە کاتێک :other یەکسانە بە :value.',
    'prohibited_if_accepted' => 'خانەی :attribute قەدەغەکراوە کاتێک :other پەسەندکراوە.',
    'prohibited_if_declined' => 'خانەی :attribute قەدەغەکراوە کاتێک :other ڕەتکراوەتەوە.',
    'prohibited_unless' => 'خانەی :attribute قەدەغەکراوە مەگەر :other لەناو :values دا بێت.',
    'prohibits' => 'خانەی :attribute ڕێگری دەکات لە بوونی :other.',
    'regex' => 'فۆرماتی خانەی :attribute نادروستە.',
    'required' => 'خانەی :attribute داواکراوە.',
    'required_array_keys' => 'پێویستە خانەی :attribute تۆمارەکان بۆ ئەم کلیلانە لەخۆبگرێت: :values.',
    'required_if' => 'خانەی :attribute داواکراوە کاتێک :other یەکسانە بە :value.',
    'required_if_accepted' => 'خانەی :attribute داواکراوە کاتێک :other پەسەندکراوە.',
    'required_if_declined' => 'خانەی :attribute داواکراوە کاتێک :other ڕەتکراوەتەوە.',
    'required_unless' => 'خانەی :attribute داواکراوە مەگەر :other لەناو :values دا بێت.',
    'required_with' => 'خانەی :attribute داواکراوە کاتێک :values ئامادەیە.',
    'required_with_all' => 'خانەی :attribute داواکراوە کاتێک :values ئامادەن.',
    'required_without' => 'خانەی :attribute داواکراوە کاتێک :values ئامادە نییە.',
    'required_without_all' => 'خانەی :attribute داواکراوە کاتێک هیچ کام لە :values ئامادە نین.',
    'same' => 'پێویستە خانەی :attribute و :other وەک یەک بن.',
    'size' => [
        'array' => 'پێویستە خانەی :attribute :size دانە لەخۆبگرێت.',
        'file' => 'پێویستە خانەی :attribute :size کیلۆبایت بێت.',
        'numeric' => 'پێویستە خانەی :attribute :size بێت.',
        'string' => 'پێویستە خانەی :attribute :size پیت بێت.',
    ],
    'starts_with' => 'پێویستە خانەی :attribute بە یەکێک لەمانە دەست پێبکات: :values.',
    'string' => 'پێویستە خانەی :attribute ڕستەیەک (string) بێت.',
    'timezone' => 'پێویستە خانەی :attribute ناوچەیەکی کاتی دروست بێت.',
    'unique' => 'خانەی :attribute پێشتر بەکارهاتووە.',
    'uploaded' => 'خانەی :attribute سەرکەوتوو نەبوو لە بارکردن.',
    'uppercase' => 'پێویستە خانەی :attribute بە پیتی گەورە بێت.',
    'url' => 'پێویستە خانەی :attribute URLێکی دروست بێت.',
    'ulid' => 'پێویستە خانەی :attribute ULIDێکی دروست بێت.',
    'uuid' => 'پێویستە خانەی :attribute UUIDێکی دروست بێت.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines - Sorani Kurdish
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'پەیامی-تایبەت',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes - Sorani Kurdish
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [], // بۆ نموونە: 'email' => 'ئیمەیڵ', 'password' => 'وشەی نهێنی'

];
