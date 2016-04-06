<?php

namespace MasterYuri\EditTrans;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Filesystem\Filesystem;

use Intervention\Image\ImageManagerStatic as Image;

class Controller extends BaseController
{
    const NAME_DELIMER = '|';

    public static function langsBase()
    {
        return base_path() . "/resources/lang/";
    }
	
    public static function viewsBase()
    {
        return base_path() . "/resources/views/";
    }

    public static function langTitles()
    {
        $base = self::langsBase();

        $fs = new Filesystem();
        $files = $fs->directories($base);

        $skip = self::cfg('skip.locales');
        
        $langs = [];
        foreach ($files as $f)
        {
            $f = basename($f);
            if (!in_array($f, $skip))
            {
                $langs[$f] = $f;
            }
        }
        
        $titles = self::cfg('lang_titles');
        foreach ($titles as $lang => $title)
        {
            if (!empty($langs[$lang]))
            {
                $langs[$lang] = $title;
            }
        }
        return $langs;
    }
	
    public static function isLocalPathAllowed($localPath)
    {
		$ret = true;
		foreach (self::cfg('skip.paths') as $skip)
		{
			$skip = ltrim($skip, "/");
			if (strpos($localPath, $skip) === 0 || strpos($localPath . ".php", $skip) === 0)
			{
				$ret = false;
				break;
			}
		}
		return $ret;
	}

    public static function pathToLocal($base, $path)
    {
        $base = str_replace("\\", "/", $base);
        $path = str_replace("\\", "/", $path);

        if (strpos($path, $base) !== 0)
        {
            throw new Exception("Invalid path!");
        }

        $count = 1;
        $ret = str_replace($base, "", $path, $count);
        return $ret;
    }

    public static function saveLangFile($fullPath, $vars, $isAppend = false)
    {
		// https://laravel.com/docs/5.1/filesystem
        $fs = new Filesystem();

        $base = self::langsBase();
        $langPath = $fullPath;
        
        if ($isAppend) 
        {
            $langData = $fs->exists($langPath) ? include $langPath : [];
            $vars     = array_merge($vars, $langData); //@todo What is about many-levels arrays?
        }
        
        $str = '<?php';
        $str .= "\n\n";
        
        $str .= "return ";
        $str .= var_export($vars, true);
        $str .= ";\n";
        
		@$fs->makeDirectory(dirname($langPath));
        return @$fs->put($langPath, $str);
    }

    public static function buildFullPathToLang($path, $lang) // path is like 'auth/validation'
    {
        $basePage = "/" . trim($path, " /\\");

        $fs = new Filesystem();
        
        $base  = self::langsBase();
        $langDir = $base . $lang;
        
        return $langDir . $basePage . '.php';
    }

    //**

    public static function cfg($name)
    {
        return config('admin-edit-trans.' . $name);
    }

    protected static function layoutName()
    {
        $extendsLayoutName = self::cfg('layout.extends');
        if (!view()->exists($extendsLayoutName)) 
        {
            throw new \Exception("Layout '{$extendsLayoutName}' not found! Set correct in value in 'layout.extends' config!");
        }
        return $extendsLayoutName;
    }

    public static function varsToInfo($vars, $prefix = "")
    {
        $ret = [];
        foreach ($vars as $key => $value)
        {
            $fullKey = trim($prefix . self::NAME_DELIMER . $key, self::NAME_DELIMER);
            if (is_array($value))
            {
                $ret[$fullKey] = 
                [
                    'title' => $key,
                    'list'  => self::varsToInfo($value, $fullKey)
                ];
            }
            else
            {
                $useRich = false;
                if (self::cfg('rich_editor.use_if_has_tags'))
                {
                    $useRich = $value != strip_tags($value);
                }
                
                $patternUse = self::cfg('rich_editor.pattern.use');
                if ($patternUse != '')
                {
                    if (preg_match($patternUse, $key) > 0) $useRich = true;
                }
                $patternSkip = self::cfg('rich_editor.pattern.skip');
                if ($patternSkip != '')
                {
                    if (preg_match($patternSkip, $key) > 0) $useRich = false;
                }
               
                $info = 
                [
                    'title' => $key,
                    'data'  =>
                    [
                        'value'         => $value,
                        'use_rich_edit' => $useRich,
                    ]
                ];
                $ret[$fullKey] = $info;
            }
        }
        return $ret;
    }

    public static function fullVarsFromInput($request, $vars, $prefix = "")
    {
        $ret = [];
        foreach ($vars as $key => $value)
        {
            $fullKey = trim($prefix . self::NAME_DELIMER . $key, self::NAME_DELIMER);
            if (is_array($value))
            {
                $ret[$key] = self::fullVarsFromInput($request, $value, $fullKey);
            }
            else
            {
                $ret[$key] = $request->input($fullKey);
            }
        }
        return $ret;
    }
    
    //**

    public static function getTransFileVars($path, $lang) // path is like 'auth/validation'
    {
        $fs = new Filesystem();
        $defLang = config('app.fallback_locale');

        //**
        
        $langPath    = self::buildFullPathToLang($path, $lang);
        $langDefPath = self::buildFullPathToLang($path, $defLang);
        
        $langData    = $fs->exists($langPath)    ? include $langPath    : [];
        $langDefData = $fs->exists($langDefPath) ? include $langDefPath : [];
        
        // We need default language to add lost variables
        
        foreach ($langDefData as $k => $v) { $langDefData[$k] = ""; }        
        $data = array_merge($langDefData, $langData);
        
        return $data;
    }
    
    //**

    public function pageList(Request $request)
    {
        $langs = self::langTitles();
        $base  = self::langsBase();
        $fs    = new Filesystem();
        $paths = [];

        foreach ($langs as $lang => $title)
        {
            $langDir = $base . $lang;
        
            $list = $fs->allFiles($langDir);
            foreach ($list as $l)
            {
                if ($l->getExtension() == 'php')
                {
                    $realPath = $l->getRealPath();
                    $realPath = mb_substr($realPath, 0, -4); // remove '.php'
                    $localPath = $this->pathToLocal($langDir, $realPath);
                    $localPath = ltrim($localPath, "/");

					if (self::isLocalPathAllowed($localPath))
                    {
                        $paths[$localPath] = 1;
                    }
                }
            }
        }

        $paths = array_keys($paths);
        sort($paths);

        $msg    = session('msg');
        $edited = session('edited');
        
        $single = count($langs) > 1;
        
        return view("edit-trans::list", 
        [
            'paths'  => $paths, 
            'langs'  => $langs, 
            'msg'    => $msg, 
            'edited' => $edited, 
            'single' => $single,
            
            'layout'          => self::layoutName(),
            'section_content' => self::cfg('layout.sections.content'),
            'section_scripts' => self::cfg('layout.sections.scripts'),
            'section_styles'  => self::cfg('layout.sections.styles'),

            'load_jquery'      => self::cfg('layout.load_resources.jquery'),
            'load_bootstrap3'  => self::cfg('layout.load_resources.bootstrap3'),
            'load_ckeditor4'   => self::cfg('layout.load_resources.ckeditor4'),
        ]);
    }

    public function pageEdit(Request $request)
    {
        $langs = self::langTitles();
        $base  = self::langsBase();

        $path = $request->get('path');
        $lang = $request->get('lang');

        $vars = self::getTransFileVars($path, $lang);
        
        $errors = [];
        if ($request->isMethod('post'))
        {
            $vars = self::fullVarsFromInput($request, $vars);
            
            $saveLang = $request->input('___save_lang', $lang);

            $savePathFull = self::buildFullPathToLang($path, $saveLang);
            $r = self::saveLangFile($savePathFull, $vars, false);

            if ($r)
            {
                return redirect()->action('\MasterYuri\EditTrans\Controller@pageList')
                                 ->with('msg', "Page has been successfully edited and saved for language '" . $langs[$saveLang] . "'!")
                                 ->with('edited', $path);
            }
            $errors = ["Can't save file!"];
        }

        // Transform vars for better presentation
        $vars = self::varsToInfo($vars);
        
        return view("edit-trans::edit", 
        [
            'langs'  => $langs, 
            'path'   => $path, 
            'lang'   => $lang, 
            'vars'   => $vars,
            'errors' => $errors, 

            'allow_save_for_lang' => self::cfg('allow_save_for_lang') && count($langs) > 1,

            'richedit_upload_url' => action('\MasterYuri\EditTrans\Controller@uploadPhotoCKEditor4', ['_token' => csrf_token()]),
            
            'layout'          => self::layoutName(),
            'section_content' => self::cfg('layout.sections.content'),
            'section_scripts' => self::cfg('layout.sections.scripts'),
            'section_styles'  => self::cfg('layout.sections.styles'),

            'load_jquery'      => self::cfg('layout.load_resources.jquery'),
            'load_ckeditor4'   => self::cfg('layout.load_resources.ckeditor4'),
            'load_bootstrap3'  => self::cfg('layout.load_resources.bootstrap3'),
        ]);
    }

    public function uploadPhotoCKEditor4(Request $request)
    {
        $msg = "";
        $url = "";

        $uploadLocation = self::cfg('upload.location');
        $maxSizeKb      = self::cfg('upload.max_size_kb');

        $resizeWidth  = self::cfg('upload.resize_down.width');
        $resizeHeight = self::cfg('upload.resize_down.height');

        if (strpos($uploadLocation, public_path()) !== 0)
        {
            throw new \Exception("Invalid configuration! Upload location must be public!");
        }

        // Required: anonymous function reference number as explained above.
        $funcNum  = $request->get('CKEditorFuncNum');
        // Optional: instance name (might be used to load a specific configuration file or anything else).
        $CKEditor = $request->get('CKEditor');
        // Optional: might be used to provide localized messages.
        $langCode = $request->get('langCode');
        
        $image = $request->file('upload');
        if ($image->isValid())
        {
            // https://github.com/Intervention/image
            $fileName = "richedit_" . date("Ymd_His_") . str_random(5) . '.' . $image->getClientOriginalExtension();
            $path = $uploadLocation . $fileName;

            try 
            {
                $im = Image::make($image->getRealPath());
                if ($im->filesize() <= $maxSizeKb * 1024)
                {
					if ($resizeWidth && $resizeHeight)
					{
						$im->resize($resizeWidth, $resizeHeight, function ($c) 
						{
							$c->aspectRatio();
							$c->upsize();
						});
					}
                    $im->save($path);
                    $url = asset(str_replace(public_path(), '', $path));
                }
                else
                {
                    $msg = "File is too big";
                }
            }
            catch (Exception $e)
            {
                $msg = $e->getMessage();
            }
        }
        else
        {
            $msg = "File is not valid";
        }

        $alert = empty($msg) ? "" : "alert('{$msg}'); ";
        
        echo "<script type='text/javascript'>" . 
                "{$alert} window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}', '{$msg}');" .
             "</script>";
        exit(0);
    }
}
