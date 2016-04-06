<?php

namespace MasterYuri\EditTrans\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Filesystem\Filesystem;

use MasterYuri\EditTrans\Controller as EditTransHelper;

class ViewsToLocales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'viewstolocales:run {--onlylocale= : Name of locale to save}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get wrapped strings from views and move them to lang files';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);
    
        $fs = new Filesystem();
        $onlyLocale = $this->option('onlylocale');
        $langTitles = EditTransHelper::langTitles();
        $viewBase   = EditTransHelper::viewsBase();
        $langBase   = EditTransHelper::langsBase();
        
        if ($onlyLocale && empty($langTitles[$onlyLocale]))
        {
            $this->error("Locale '{$onlyLocale}' not found or disabled in config!");
        }
        else
        {
            $saveLocales = $onlyLocale ? [$onlyLocale] : array_keys($langTitles);

            //$paths = [];
            //@todo
            //$paths[] = base_path() . "/resources/views/site/home.blade.php";
            $paths = $fs->allFiles($viewBase);
            
            $this->comment("Preparing list of files...");
            $this->info(" Sorce view file\t| Target file\t| Target name\t| Target value");
            $this->info(" -----------------------------------------------------------------------");

            $list = [];
            foreach ($paths as $path)
            {
                if (ends_with($path, '.php'))
                {
                    $content = '';
                    $vars = self::getLangsFromFile($path, $content);
                    $localPath = EditTransHelper::pathToLocal($viewBase, $path);

                    if (count($vars) && EditTransHelper::isLocalPathAllowed($localPath))
                    {
                        $list[] = 
                        [
                            'path'    => $path,
                            'content' => $content,
                            'vars'    => $vars,
                        ];
                       
                        foreach ($vars as $v => $value)
                        {
                            $varInfo = self::prepareLangVarData($path, $v);
                        
                            $value = preg_replace('/\s+/', ' ', $value);
                            $value = str_limit(trim(str_replace(["\r", "\n", "\t"], "", $value)), 32);
                            
                            $this->info(" {$localPath}\t| " . ($varInfo['dir'] != "/" ? $varInfo['dir'] : "") . "/" . $varInfo['file'] . ".php" . "\t| " . $varInfo['name'] . "\t| " . str_limit($value, 32));
                        }
                        $this->info(" -----------------------------------------------------------------------");
                    }
                }
            }

            if (count($list) == 0)
            {
                $this->info(" Nothing to process! Exit...");
            }
            else
            {
                $this->line('');
                $this->line('Don\'t forget to make backup copy of view and lang files!');
                if ($this->confirm('Do you want to continue? [y|N]')) 
                {
                    foreach ($list as $l)
                    {
                        $path    = $l['path'];
                        $vars    = $l['vars'];
                        $content = $l['content'];
               
                        $localPath = EditTransHelper::pathToLocal($viewBase, $path);
                                                            
                        $viewerPath = $viewBase . $localPath;
                        
                        $prevContent = $fs->get($viewerPath);
                        $r = $fs->put($viewerPath, $content);
                        if (!$r)
                        {
                            $this->error("Can't rewrite '{$viewerPath}' file! Check access rights!");
                        }
                        else
                        {
                            $this->info("Viewer file '{$localPath}' has been rewriten...");
               
                            $set = [];
                            foreach ($vars as $v => $value)
                            {
                                $varInfo = self::prepareLangVarData($path, $v);
                                array_set($set, $varInfo['name'], $value);
                            }
               
                            foreach ($saveLocales as $lang)
                            {
                                $langLocalPath = $lang . "/" . ltrim($varInfo["dir"] . "/" . $varInfo["file"] . ".php", "/");
                                $langPath = $langBase . $langLocalPath;
               
                                $r = EditTransHelper::saveLangFile($langPath, $set, true);
                                if ($r)
                                {
                                    $this->info("Localization file '{$langLocalPath}' has been rewriten...");
                                }
                                else
                                {
                                    $this->error("Can't rewrite '{$langPath}' file! Check access rights! Viewer file will be restored...");
                                    $r = $fs->put($viewerPath, $prevContent);
                                    break;
                                }
                            }
                            $this->line("......");
                        }
                    }
                    $this->info('Done!');
                }
            }
        }
    }

    //**

    public static function prepareLangVarData($path, $name) // Returns array of found strings to move
    {
        $path = str_replace("\\", "/", $path); // For sure
        $viewBase = EditTransHelper::viewsBase();
        
        $localPath = EditTransHelper::pathToLocal($viewBase, $path);

        //**

        $saveDir  = dirname($localPath);
        $saveFile = basename($localPath, ".php");
        $saveFile = basename($saveFile, ".blade");
        $saveName = $name;

        if (strpos($name, ".") !== false)
        {
            $saveName = $name;
            
            $list  = explode(".", $name);
            $first = reset($list);
            if ($first)
            {
                $saveFile = $first;
            }
            unset($list[0]);
            $name2 = implode(".", $list);
            if ($name2)
            {
                $saveName = $name2;
            }
        }

        if (strpos($name, "/") !== false)
        {
            $list = explode("/", $name);
            array_pop($list);
            $saveDir = implode("/", $list);
        }

        $saveDir  = "/" . trim($saveDir, "/\\");
        $saveFile = trim($saveFile, "/\\");

        return 
        [
            'dir'  => $saveDir,
            'file' => $saveFile,
            'name' => $saveName,
        ];
    }

    public static function getLangsFromFile($path, &$outText = NULL) // Returns array of found strings to move
    {
        $fs = new Filesystem();
        $pattern  = EditTransHelper::cfg('viewstolangs');

        $content = $fs->get($path);
        $contentBase = $content;

        $savedVars = [];
        $outText = preg_replace_callback
        (
            $pattern, 
            function($pockets) use ($path, &$savedVars)
            {
                $name = $pockets[1];
                if (empty($name))
                {
                    $name = $pockets[3];
                }
                if (empty($name))
                {
                    $name = self::strToVarName($pockets[2]);
                }
                $name = trim($name);
               
                if (empty($name))
                {
                    return "";
                }
                $savedVars[$name] = $pockets[2];

                $varInfo = self::prepareLangVarData($path, $name);
                $name = $varInfo['file'] . "." . $varInfo['name'];
                if ($varInfo['dir'])
                {
                    $name = $varInfo['dir'] . "/" . $name;
                }
                $name = ltrim($name, "/\\");

                return "@lang('{$name}')";
            }, 
            $content
        );
        return $savedVars;
    }

    public static function strToVarName($str)
    {
        $str = trim(strip_tags($str));
        $str = str_slug($str);

        $tr = 
        [
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
            "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
        ];
        $str = strtr($str, $tr);
        $str = str_limit($str, 32, '');
        $str = str_replace('-', '_', $str);
        return $str;
    }
}
