<?php

return [

    // In case your have own admin section and want to embed this library into it you can configure 'layout' parameter.
    //  For example if you have admin section with layout in viewer file '/admin/layout.blade.php' you can change 'layout.extends' to 'admin.layout'. 
    //  It must have @yield for sections 'content', 'styles' and 'scripts' like in this article:
    //  http://laravelcoding.com/blog/laravel-5-beauty-starting-the-admin-area
    'layout' => 
    [
        'extends' => 'edit-trans::default-layout', // 'admin.layout' // Admin section layout viwer name (for @extends call) with following yields.

        'sections' => 
        [
            'content' => 'content', // For @yield('content') in layout
            'scripts' => 'scripts', // For @yield('scripts') in layout
            'styles'  => 'styles',  // For @yield('scripts') in layout
        ],
        
        // You can disable following if layout that you use already includes jQuery, CKEditor 4 and bootstrap 3.
        'load_resources' =>
        [
            'jquery'     => true,
            'ckeditor4'  => true,
            'bootstrap3' => true,
        ],
    ],

    // For 'Route::group()' call to declare pages.
    'route' => 
    [
        'prefix'     => 'admin',
        'middleware' => ['web'],
    ],

    // Do not really need to fill for all languages.
    //  But with correct name it looks better in admin section.
    'lang_titles' => 
    [
        'ru' => 'Русский',
        'en' => 'English',
    ],
    
    // If you want to use CKEditor 4.
    'rich_editor' =>
    [
        'use_if_has_tags' => true, // Show rich editor instead of textarea if value has html tags.

        'pattern' =>
        [
            // If variable name matches pattern (in preg_match()) force use rich edit instead of textarea.
            'use'  => "|-notg\$|",
           
            // If variable name matches pattern (in preg_match()) force use textarea instead of rich edit (in case use_if_has_tags is true).
            'skip' => '|-richedit\$|',
        ],
    ],

    // To upload photos in rich edit.
    'upload' =>
    [
        // Public path to store images.
        'location'    => public_path() . '/uploads/',
        // Max size to upload.
        'max_size_kb' => 4096,
        
        // To scale down afrer uploading.
        'resize_down' =>
        [
            'width'  => 1200,
            'height' => 900,
        ],
    ],
    
    'skip' =>
    [
        // Do not show files of folowing locales for edit.
        'locales' =>
        [
            //'ru',
        ],

        // Do not show files of subdirectories that starts from these strings.
        // Paths are local in language directury.
        'paths' =>
        [
            'admin/', // Means skip '/resources/lang/en/admin/*" and '/resources/lang/ru/admin/*".
            // 'auth'     // means 'auth.php' or 'authblablabla'.
            // 'auth.php' // this way is allowed too.
            // 'pages/',  // all files in directory 'pages'.
        ],
    ],
    
    // Allow to open for one language but save to another.
    'allow_save_for_lang' => true,
    
    // For console (artisan) util:
    'viewstolangs' =>
    [
        // To use with preg_replace_callback.
        // Wrap text to: <h1>{{--@/home.title@--}}The title{{--@@--}}</h1>
        'pattern' => "|
                          {{--@(.*?)@--}}
                              (.*?)
                          {{--@(.*?)@--}}
                      |isx",
    ],
];
