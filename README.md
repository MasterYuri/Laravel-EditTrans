# Laravel-EditTrans Beta

Component to manage localization files for projects based on Laravel 5 with rich editor support.

![alt tag](http://ambermuseum.ru/upl/ckeditor/2016-04-05_19-50-58.png)
![alt tag](http://ambermuseum.ru/upl/ckeditor/2016-04-05_19-54-41.png)
![alt tag](http://ambermuseum.ru/upl/ckeditor/2016-04-05_19-58-16.png)

## Configuration

@todo

## Artisan utils

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

With option "onlylocale" you can make it to generate only for one locale:

```
php artisan viewstolocales:run --onlylocale=en
```

Also in tag wrap tag you can declare name of variable and save path. For example:

We have viewer at '/resources/views/site/home.blade.php' and one string to move into localization file (Local path in views is '/site/home.blade.php').
Let's find out what we get after running "php artisan viewstolocales:run --onlylocale=en":

Source string        |    Final string    | Final localization file | Comment
---------------------|--------------------|-------------------------|---------------------|
<h1>{{--@@--}}Some title{{--@@--}}</h1>             | <h1>@lang('site/home.some_title')</h1>  | /resources/lang/en/site/home.php     ["some_title" => "Some title"]   | Use original dir and file name, generate var name based on var text. |
<h1>{{--@the_title@--}}Some title{{--@@--}}</h1>    | <h1>@lang('site/home.the_title')</h1>   | /resources/lang/en/site/home.php     ["the_title"  => "Some title"]   | Use original dir and file name, set var name. |
<h1>{{--@.the_title@--}}Some title{{--@@--}}</h1>   | <h1>@lang('site/home.the_title')</h1>   | /resources/lang/en/site/home.php     ["the_title"  => "Some title"]   | Same |
<h1>{{--@/.the_title@--}}Some title{{--@@--}}</h1>  | <h1>@lang('home.the_title')</h1>        | /resources/lang/en/home.php          ["the_title"  => "Some title"]   | Put in root, user original file name, set var name. |
<h1>{{--@default.the_title@--}}Some title{{--@@--}}</h1>          | <h1>@lang('site/default.the_title')</h1>       /resources/lang/en/site/default.php    | ["the_title" => "Some title"]     | Use original dir, set file name and var name. |
<h1>{{--@/default.the_title@--}}Some title{{--@@--}}</h1>         | <h1>@lang('site/default.the_title')</h1>       /resources/lang/en/site/default.php      |  ["the_title" => "Some title"]  | Put in root, set file name and var name. |
<h1>{{--@the_site/default.the_title@--}}Some title{{--@@--}}</h1> | <h1>@lang('the_site/default.the_title')</h1>   /resources/lang/en/the_site/default.php  |  ["the_title" => "Some title"] |  Set dir name, file name and var name. |
<h1>{{--@the_site/the_site_subdir/default.the_title@--}}Some title{{--@@--}}</h1> |  <h1>@lang('the_site/the_site_subdir/default.the_title')</h1>   /resources/lang/en/the_site/the_site_subdir/default.php   | ["the_title" => "Some title"] |  Set dir name with subdir, set file name and var name. |
<h1>{{--@the_site/the_site_subdir/.the_title@--}}Some title{{--@@--}}</h1> |   <h1>@lang('the_site/the_site_subdir/home.the_title')</h1>      /resources/lang/en/the_site/the_site_subdir/home.php      | ["the_title" => "Some title"] |  Set dir name with subdir, use original file name and set var name. |
