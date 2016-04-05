# Laravel-EditTrans Beta

Component to manage localization files for projects based on Laravel 5 with rich editor support.

![alt tag](http://ambermuseum.ru/upl/ckeditor/2016-04-05_19-50-58.png)
![alt tag](http://ambermuseum.ru/upl/ckeditor/2016-04-05_19-54-41.png)
![alt tag](http://ambermuseum.ru/upl/ckeditor/2016-04-05_19-58-16.png)

## Installation

@todo

Link to managment page is:

```
'/admin/edit_trans' // In case if config('admin-edit-trans.route.prefix') is equal to 'admin'
```

or action:

```
'Admin\EditTransController@pageList'
```

## Configuration

Configuration file is 'admin-edit-trans.php'. 
It has comments that describe all parameters. Example of config file:

```php
return [
    // In case your have own admin section and want to embed this library into it you can configure 'layout' parameter.
    //  For example if you have admin section with layout in viewer file '/admin/layout.blade.php' you can change 'layout.extends' to 'admin.layout'. 
    //  It must have @yield for sections 'content', 'styles' and 'scripts' like in this article:
    //  http://laravelcoding.com/blog/laravel-5-beauty-starting-the-admin-area
    'layout' => 
    [
        'extends' => 'admin.edit-trans.default-layout', // 'admin.layout' // Admin section layout viwer name (for @extends call) with following yields.

        'sections' => 
        [
            'content' => 'content', // For @yield('content') in layout
            'scripts' => 'scripts', // For @yield('scripts') in layout
            'styles'  => 'styles',  // For @yield('scripts') in layout
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
```

## Artisan util

Library has utilite that allows you to move strings from viewers to localization files.
In most simple case you need just to wrap string into special tag:

```
<h1>{{--@@--}}Some title{{--@@--}}</h1>
```

And run command:

```
php artisan viewstolocales:run
```

It will replace text to:

```
<h1>@lang('home.some_title')</h1>
```

And create new localization files (or append to existing) for all existing locales.

### Options

With option `onlylocale` you can make it to generate only for one locale:

```
php artisan viewstolocales:run --onlylocale=en
```

Also in tag wrap tag you can declare name of variable and save path. For example:

We have viewer at `/resources/views/site/home.blade.php` and one string to move into localization file (Local path in views is '/site/home.blade.php').
Let's find out what we get after running `php artisan viewstolocales:run --onlylocale=en`:

### Example 1

Save original subdirectory and file name, generate var name based on var text.

Source string in viewer:
```
<h1>{{--@@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('site/home.some_title')</h1>
```

Final localization file path:
```
/resources/lang/en/site/home.php
```

Final content of localization:
```
return ["some_title" => "Some title"]
```

### Example 2

Save original subdirectory and file name, use custom var name.

Source string in viewer:
```
<h1>{{--@the_title@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('site/home.the_title')</h1>
```

Final localization file path:
```
/resources/lang/en/site/home.php
```

Final content of localization:
```
["the_title"  => "Some title"]
```

### Example 3

Same case.

Source string in viewer:
```
<h1>{{--@.the_title@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('site/home.the_title')</h1>
```

Final localization file path:
```
/resources/lang/en/site/home.php
```

Final content of localization:
```
["the_title"  => "Some title"]
```

### Example 4

Save original subdirectory and file name, use custom multil-level var name.

Source string in viewer:
```
<h1>{{--@.the_title.inner1.inner2@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('site/home.the_title.inner1.inner2')</h1>
```

Final localization file path:
```
/resources/lang/en/site/home.php
```

Final content of localization:
```
["the_title"  => ["inner1" => ["inner2" => ["Some title"]]]]
```

### Example 5

Put in localization file into root, save original file name, set custom var name.

Source string in viewer:
```
<h1>{{--@/.the_title@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('home.the_title')</h1>
```

Final localization file path:
```
/resources/lang/en/home.php
```

Final content of localization:
```
["the_title"  => "Some title"]
```

### Example 6

Save original subdirectory, set custom file name and set custom var name

Source string in viewer:
```
<h1>{{--@default.the_title@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('site/default.the_title')</h1>
```

Final localization file path:
```
/resources/lang/en/site/default.php
```

Final content of localization:
```
["the_title" => "Some title"]
```

### Example 7

Put in localization file into root, set custom file name and set custom var name.

Source string in viewer:
```
<h1>{{--@/default.the_title@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('site/default.the_title')</h1>
```

Final localization file path:
```
/resources/lang/en/site/default.php
```

Final content of localization:
```
["the_title" => "Some title"]
```

### Example 8

Set custom subdirectory name, set custom file name and set custom var name.

Source string in viewer:
```
<h1>{{--@the_site/default.the_title@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('the_site/default.the_title')</h1>
```

Final localization file path:
```
/resources/lang/en/the_site/default.php
```

Final content of localization:
```
["the_title" => "Some title"]
```

### Example 9

Set custom deep subdirectory name, set custom file name and set custom var name.

Source string in viewer:
```
<h1>{{--@the_site/the_site_subdir/default.the_title@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('the_site/the_site_subdir/default.the_title')</h1>
```

Final localization file path:
```
/resources/lang/en/the_site/the_site_subdir/default.php
```

Final content of localization:
```
["the_title" => "Some title"]
```

### Example 10

Set custom deep subdirectory name, set custom file name and set custom multi-level var name.

Source string in viewer:
```
<h1>{{--@the_site/the_site_subdir/default.the_title.inner1.inner2@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('the_site/the_site_subdir/default.the_title.inner1.inner2')</h1>
```

Final localization file path:
```
/resources/lang/en/the_site/the_site_subdir/default.php
```

Final content of localization:
```
["the_title"  => ["inner1" => ["inner2" => ["Some title"]]]]
```

### Example 11

Set custom deep subdirectory name, save original file name, set custom var name.

Source string in viewer:
```
<h1>{{--@the_site/the_site_subdir/.the_title@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('the_site/the_site_subdir/home.the_title')</h1>
```

Final localization file path:
```
/resources/lang/en/the_site/the_site_subdir/home.php
```

Final content of localization:
```
["the_title" => "Some title"]
```

### Example 12

Set custom deep subdirectory name, save original file name, set custom var name.

Source string in viewer:
```
<h1>{{--@the_site/the_site_subdir/.@--}}Some title{{--@@--}}</h1>
```

Final string in viwer:
```
<h1>@lang('the_site/the_site_subdir/home.the_title')</h1>
```

Final localization file path:
```
/resources/lang/en/the_site/the_site_subdir/home.php
```

Final content of localization:
```
["the_title" => "Some title"]
```

--------------------------

Also remember that you can wrap big peaces of text that contains html tags:

```html
{{--@@--}}
<p>
  Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut 
  labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris 
  nisi ut aliquip ex ea commodo consequat. 
</p>
<p>
  Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, 
  totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae 
  dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, 
  sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. 
</p>
{{--@@--}}
```
