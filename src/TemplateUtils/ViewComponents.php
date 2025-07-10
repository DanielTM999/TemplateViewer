<?php

    namespace Daniel\TemplateViewer\TemplateUtils;

    use Daniel\Origins\Module;
    use Daniel\Origins\ModuleManager;
    use Daniel\TemplateViewer\MainFrame;

    class Html{
        public static function render(string $html, Module|null $module = null, array $customProps = [], bool $autorender = true): string{

            if(ModuleManager::isModuleAvailable()){
                if($module == null){
                    $controllerModule = MainFrame::getControllerModule();
                    if($controllerModule == null){
                        $module = ModuleManager::getCurrentModule();
                    }else{
                        $module = $controllerModule;
                    }
                }
                $path = $module->getModuleProperty("views", $html);
            }else{
                $viewFolder = $_ENV["MainFrame.views"] ?? $_ENV["base.dir"]."/src/views";
                $path = $viewFolder."$html";
            }

            extract($customProps);

            ob_start();
            include $path;
            $output = ob_get_clean();
            
            if ($autorender) {
                echo $output;
            }

            return $output;
        }

        public static function addIcon(string $src, Module|null $module = null){
            if(ModuleManager::isModuleAvailable()){
                if($module == null){
                    $controllerModule = MainFrame::getControllerModule();
                    if($controllerModule == null){
                        $module = ModuleManager::getCurrentModule();
                    }else{
                        $module = $controllerModule;
                    }
                }
                $path = $module->getModuleProperty("images", $src);
                $path = Paths::normalizePathToPublic($path);
            }else{
                $viewFolder = $_ENV["MainFrame.Module.images"] ?? "/src/views/images";
                $path = $viewFolder."$src";
            }

            echo "<link rel='shortcut icon' href='$path' type='image/x-icon'>";
        }

    }

    class Css{

        public static function linkCss(string|null $filename = null, ?Module $moduleTarger = null){

            if(ModuleManager::isModuleAvailable()){
                if($moduleTarger == null){
                    $controllerModule = MainFrame::getControllerModule();
                    if($controllerModule == null){
                        $module = ModuleManager::getCurrentModule();
                    }else{
                        $module = $controllerModule;
                    }
                }else{
                    $module = $moduleTarger;
                }


                if($filename == null || empty($filename)){
                    $callFile = $module->getCallableFileName(false, 3);
                    $path = $module->getModuleProperty("views", $callFile);
                    if(!file_exists($path)){
                        $path = $module->getModuleProperty($_ENV["MainFrame.templatesFolder"] ?? "views", $callFile);
                    }
                    $path = "$path/$callFile.css";
                }else{
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if ($ext === 'css') {
                        $path = $module->getModuleProperty("resources", $filename);
                    } else {
                        $callFile = $module->getCallableFileName(false, 3);
                        $path = $module->getModuleProperty("views", "$filename/$callFile");
                        $path = "$path/$callFile.css";
                    } 
                }
    
                if (!file_exists($path)) {
                    throw new \RuntimeException("Arquivo de CSS não encontrado: $path");
                }
    
            }else{
                $viewFolder = $_ENV["MainFrame.views"] ?? $_ENV["base.dir"]."/src/views";
                $path = $viewFolder."$filename";
                if (!file_exists($path)) {
                    throw new \RuntimeException("Arquivo de CSS não encontrado: $path");
                }
            }
            echo "<style>\n" . file_get_contents($path) . "\n</style>";
        }

    }

    class Script{

        public static function injectJs(string|null $filename = null, bool $byfullPath = false , ?Module $moduleTarger = null) {
            if(ModuleManager::isModuleAvailable()){
                if($moduleTarger == null){
                    $controllerModule = MainFrame::getControllerModule();
                    if($controllerModule == null){
                        $module = ModuleManager::getCurrentModule();
                    }else{
                        $module = $controllerModule;
                    }
                }else{
                    $module = $moduleTarger;
                }

                if($filename == null || empty($filename)){
                    $callFile = $module->getCallableFileName(false, 3);
                    $path = $module->getModuleProperty("views", $callFile);
                    if(!file_exists($path)){
                        $path = $module->getModuleProperty($_ENV["MainFrame.templatesFolder"] ?? "views", $callFile);
                    }
                    $path = "$path/$callFile.js";
                }else{
                    if($byfullPath){
                        $modulePath = $module->getModulePath();
                        $endsWithSlash = preg_match('#[\\/]{1}$#', $modulePath);
                        $startsWithBackslash = str_starts_with($filename, '\\');
                        if ($endsWithSlash && $startsWithBackslash) {
                            $filename = ltrim($filename, '\\');
                            $path = $modulePath . $filename;
                        } elseif (!$endsWithSlash && !$startsWithBackslash) {
                            $path = $modulePath . DIRECTORY_SEPARATOR . $filename;
                        } else {
                            $path = $modulePath . $filename;
                        }
                    }else{
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        if ($ext === 'js') {
                            $path = $module->getModuleProperty("resources", $filename);
                            if(!file_exists($path)){
                                $path = $module->getModuleProperty("views", $filename);
                            }
                        } else {
                            $callFile = $module->getCallableFileName(false, 3);
                            $path = $module->getModuleProperty("views", "$filename/$callFile");
                            $path = "$path/$callFile.js";
                        } 
                    }
                }

                if (!file_exists($path)) {
                    throw new \RuntimeException("Arquivo de script não encontrado: $path");
                }
                $publicPath = Paths::normalizePathToPublic($path);

                if (!str_starts_with($publicPath, '/')) {
                    $publicPath = '/' . $publicPath;
                }

                echo "<script src='$publicPath'></script>";
            }else{
                $viewFolder = $_ENV["MainFrame.views"] ?? $_ENV["base.dir"]."/src/views";
                $path = $viewFolder."$filename";
                if (!file_exists($path)) {
                    throw new \RuntimeException("Arquivo de Js não encontrado: $path");
                }
            }
        }

    }

    class Paths {
        public static function normalizePathToPublic(string $path): string {
            if (str_starts_with($path, '/')) {
                $publicPath = $path;
            } else {
                $realPath = realpath($path);
                if ($realPath === false) {
                    return '/';
                }
                $normalizedPath = str_replace('\\', '/', $realPath);
                if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== '') {
                    $normalizedRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
                    $publicPath = str_replace($normalizedRoot, '', $normalizedPath);
                } else {
                    $publicPath = $normalizedPath;
                }
            }
            if ($publicPath === '' || $publicPath[0] !== '/') {
                $publicPath = '/' . $publicPath;
            }
            return $publicPath;
        }
    }


?>
