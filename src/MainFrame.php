<?php
    namespace Daniel\TemplateViewer;

    use Daniel\Origins\Module;
    use Daniel\Origins\ModuleManager;
    use RuntimeException;

    final class MainFrame {

        private static ?Module $controllerModule;

        public static function render(string|Module|null $moduleRef, string $title, string $content, $viewModel = null, string|null $mainFrameName = null, &$customArgs = []){
            if(ModuleManager::isModuleAvailable()){
               self::renderWithModule($moduleRef, $title, $content, $viewModel, $mainFrameName, $customArgs); 
            }else{
                self::renderWithOutModule($title, $content, $viewModel, $mainFrameName, $customArgs);
            }
        }

        public static function getMainModule(): Module|null{
            if(ModuleManager::isModuleAvailable()){
                $mainFrameModule = $_ENV["MainFrame.Module"] ?? null;
                return ($mainFrameModule == null) ? null : ModuleManager::getModuleByName($mainFrameModule);
            }
            return null;
        }

        private static function renderWithModule(string|Module|null $moduleRef, string $title, string $content, $viewModel = null, string|null $mainFrameName = null, &$customArgs = []){
            if(!isset($_ENV["MainFrame.Module"])){
                throw new \RuntimeException("A variável de ambiente 'MainFrame.Module' não está definida. 
                Certifique-se de que ela foi configurada corretamente no ambiente ou no arquivo .env.");
            }
            $mainFrameModule = $_ENV["MainFrame.Module"];
            $frameModule = ModuleManager::getModuleByName($mainFrameModule);
            if($moduleRef == null){
                $module = ModuleManager::getCurrentModule();
            }elseif($moduleRef instanceof Module){
                $module = $moduleRef;
            }else{
                $module = ModuleManager::getModuleByReference($moduleRef);
            }
            $viewFolder = $_ENV["MainFrame.templatesFolder"] ?? "views";

            $mainFrameName = ($mainFrameName == null) ? self::getMainFrameName($module) : $mainFrameName;
            
            $folderName = ucfirst($mainFrameName);
            $masterPage = $frameModule->getModuleProperty($viewFolder, "$folderName/$mainFrameName.php");
            
            if($masterPage == null){
                throw new \RuntimeException(
                    "Não foi possível localizar a master page '$folderName/$mainFrameName.php'. " .
                    "Verifique se o diretório de views está corretamente definido em 'MainFrame.viewsFolder' no .env " .
                    "ou se a entrada correspondente está presente no modules.config do módulo '$mainFrameModule'."
                );
            }

            self::includeWithVars($masterPage, [
                'mainFrame' => [
                    "MainFrame.title" => $title,
                    "MainFrame.content" => $content
                ],
                'frameModule' => $module,
                'viewModel' => $viewModel,
                'args' => $customArgs,
                'mainFrameModule' => MainFrame::getMainModule()
            ]);
        }

        private static function renderWithOutModule(string $title, string $content, $viewModel = null, string|null $mainFrameName = null, &$customArgs = []){
            $viewFolder = $_ENV["MainFrame.templatesFolder"] ?? "views";
            $mainFrameName = self::getMainFrameName(null);
            $masterPage = "$viewFolder/$mainFrameName.php";
            self::includeWithVars($masterPage, [
                'mainFrame' => [
                    "MainFrame.title" => $title,
                    "MainFrame.content" => $content
                ],
                'args' => $customArgs,
                'viewModel' => $viewModel,
            ]);
        }

        private static function getMainFrameName(Module|null $module){
            $in = ".env";
            if($module != null){
                $default = $module->getModuleProperty("masterPage") ?? null;
                $in = "modules.config";
            }else{
                $default  = $_ENV["masterPage"] ?? null;
            }

            if($default == null) {
                if ($default === null) {
                    throw new \RuntimeException(
                        "Arquivo '{$in}': propriedade 'masterPage' não definida. " .
                        "Defina a master page padrão usando a chave 'masterPage' no '{$in}', " .
                        "na configuração do módulo correspondente, " .
                        "ou usando a anotação #[MasterPage(\"Masterpage\")] no método do controller."
                    );
                }
            }

            return $default;
        }

        private static function includeWithVars(string $file, array $vars = []): void {
            extract($vars);
            include_once $file;
        }

        public static function getControllerModule(): Module|null {
            return self::$controllerModule;
        }

        public static function setControllerModule(?Module $controllerModule) {
            self::$controllerModule = $controllerModule;
        }
    }

?>