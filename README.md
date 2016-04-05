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

With option `onlylocale` you can make it to generate only for one locale:

```
php artisan viewstolocales:run --onlylocale=en
```

Also in tag wrap tag you can declare name of variable and save path. For example:

We have viewer at `/resources/views/site/home.blade.php` and one string to move into localization file (Local path in views is '/site/home.blade.php').
Let's find out what we get after running `php artisan viewstolocales:run --onlylocale=en`:

| Source string                                               |    Final string                         | Final localization file           | Comment             |
| ------------------------------------------------------------|-----------------------------------------|-----------------------------------|---------------------|
| `<h1>{{--@@--}}Some title{{--@@--}}</h1>`                   | `<h1>@lang('site/home.some_title')</h1>`  | `/resources/lang/en/site/home.php` |  `["some_title" => "Some title"]`   | Use original dir and file name, generate var name based on var text. |
| `<h1>{{--@the_title@--}}Some title{{--@@--}}</h1>`          | `<h1>@lang('site/home.the_title')</h1>`   | `/resources/lang/en/site/home.php` |  `["the_title"  => "Some title"]`   | Use original dir and file name, set var name. |


Also remember that you can wrap big peaces of text that contains html tags:

```
{{--@@--}}
<p>
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
</p>
<p>
Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.
</p>
{{--@@--}}
```
