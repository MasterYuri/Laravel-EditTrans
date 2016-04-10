# Laravel-EditTrans Beta

Component to manage localization files for projects based on Laravel 5 with rich editor support.

![alt tag](http://ambermuseum.ru/upl/ckeditor/2016-04-05_19-50-58.png)
![alt tag](http://ambermuseum.ru/upl/ckeditor/2016-04-05_19-54-41.png)
![alt tag](http://ambermuseum.ru/upl/ckeditor/2016-04-05_19-58-16.png)

## Installation

Via Composer

``` bash
$ composer require masteryuri/laravel-edittrans
```

Add service provider into `/config/app.php`:

```
'MasterYuri\EditTrans\ServiceProvider',
```

Publish config and public resources:

```
php artisan vendor:publish --provider="MasterYuri\EditTrans\ServiceProvider"
```

## Usage

Link to managment page is:

```
'/admin/edit_trans' // In case if config('admin-edit-trans.route.prefix') is equal to 'admin'
```

or action:

```
'MasterYuri\EditTrans\Controller@pageList'
```

## Configuration

Configuration file is 'admin-edit-trans.php'. 
It has comments that describe all parameters.

[Read configuration file](config/admin-edit-trans.php)

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

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
