<?php

namespace Daniel\TemplateViewer\TemplateUtils;

use Daniel\Origins\Module;
use Daniel\Origins\ModuleManager;
use Daniel\TemplateViewer\MainFrame;

class Html {
    public static function render(string $html, Module|null $module = null, array $customProps = [], bool $autorender = true): string {
        if (ModuleManager::isModuleAvailable()) {
            if ($module === null) {
                $controllerModule = MainFrame::getControllerModule();
                $module = $controllerModule ?? ModuleManager::getCurrentModule();
            }
            $path = $module->getModuleProperty("views", $html);
        } else {
            $viewFolder = $_ENV["MainFrame.views"] ?? $_ENV["base.dir"] . "/src/views";
            $path = rtrim($viewFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $html;
        }

        if (!file_exists($path)) {
            throw new \Exception("Arquivo não encontrado: $path");
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

    public static function addIcon(string $src, Module|null $module = null) {
        if (ModuleManager::isModuleAvailable()) {
            if ($module === null) {
                $controllerModule = MainFrame::getControllerModule();
                $module = $controllerModule ?? ModuleManager::getCurrentModule();
            }
            $path = $module->getModuleProperty("images", $src);
            $path = Paths::normalizePathToPublic($path);
        } else {
            $viewFolder = $_ENV["MainFrame.Module.images"] ?? "/src/views/images";
            $path = rtrim($viewFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $src;
        }
        echo "<link rel='shortcut icon' href='$path' type='image/x-icon'>";
    }
}

class Css {
    public static function linkCss(string|null $filename = null, ?Module $moduleTarger = null) {
        if (ModuleManager::isModuleAvailable()) {
            $module = $moduleTarger ?? MainFrame::getControllerModule() ?? ModuleManager::getCurrentModule();

            if (empty($filename)) {
                $callFile = $module->getCallableFileName(false, 3);
                $viewName = mb_strtoupper(mb_substr($callFile, 0, 1)) . mb_substr($callFile, 1);
                $path = $module->getModuleProperty("views", $viewName);

                if (!file_exists($path)) {
                    $path = $module->getModuleProperty($_ENV["MainFrame.templatesFolder"] ?? "views", $viewName);
                }
                $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "$callFile.css";
            } else {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if ($ext === 'css') {
                    $path = $module->getModuleProperty("resources", $filename);
                } else {
                    $callFile = $module->getCallableFileName(false, 3);
                    $viewName = mb_strtoupper(mb_substr($callFile, 0, 1)) . mb_substr($callFile, 1);
                    $path = $module->getModuleProperty("views", "$filename/$viewName");
                    $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "$callFile.css";
                }
            }

            if (!file_exists($path)) {
                throw new \RuntimeException("Arquivo de CSS não encontrado: $path");
            }
        } else {
            $viewFolder = $_ENV["MainFrame.views"] ?? $_ENV["base.dir"] . "/src/views";
            $path = rtrim($viewFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
            if (!file_exists($path)) {
                throw new \RuntimeException("Arquivo de CSS não encontrado: $path");
            }
        }
        echo "<style>\n" . file_get_contents($path) . "\n</style>";
    }
}

class Script {
    public static function injectJs(string|null $filename = null, bool $byfullPath = false, ?Module $moduleTarger = null) {
        if ($filename !== null && !empty($filename)) {
            if (Paths::isValidUrl($filename)) {
                echo "<script src=\"$filename\"></script>";
                return;
            }
        }

        if (ModuleManager::isModuleAvailable()) {
            $module = $moduleTarger ?? MainFrame::getControllerModule() ?? ModuleManager::getCurrentModule();

            if (empty($filename)) {
                $callFile = $module->getCallableFileName(false, 3);
                $viewName = mb_strtoupper(mb_substr($callFile, 0, 1)) . mb_substr($callFile, 1);
                $path = $module->getModuleProperty("views", $viewName);
                if (!file_exists($path)) {
                    $path = $module->getModuleProperty($_ENV["MainFrame.templatesFolder"] ?? "views", $viewName);
                }
                $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "$callFile.js";
            } else {
                if ($byfullPath) {
                    $modulePath = rtrim($module->getModulePath(), DIRECTORY_SEPARATOR);
                    $filename = ltrim($filename, DIRECTORY_SEPARATOR);
                    $path = $modulePath . DIRECTORY_SEPARATOR . $filename;
                } else {
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if ($ext === 'js') {
                        $path = $module->getModuleProperty("resources", $filename);
                        if (!file_exists($path)) {
                            $path = $module->getModuleProperty("views", $filename);
                        }
                    } else {
                        $callFile = $module->getCallableFileName(false, 3);
                        $viewName = mb_strtoupper(mb_substr($callFile, 0, 1)) . mb_substr($callFile, 1);
                        $path = $module->getModuleProperty("views", "$filename/$viewName");
                        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "$callFile.js";
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
        } else {
            $viewFolder = $_ENV["MainFrame.views"] ?? $_ENV["base.dir"] . "/src/views";
            $path = rtrim($viewFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
            if (!file_exists($path)) {
                throw new \RuntimeException("Arquivo de Js não encontrado: $path");
            }
        }
    }
}

class Paths {
    public static function normalizePathToPublic(string $path): string {
        $rootDir = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $_ENV["base.dir"] ?? ''), DIRECTORY_SEPARATOR);
        $normalizedPath = preg_replace('#' . preg_quote(DIRECTORY_SEPARATOR) . '+#', DIRECTORY_SEPARATOR, str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path));
        $normalizedRoot = $rootDir;
        if ($rootDir !== '' && str_starts_with($normalizedPath, $normalizedRoot)) {
            $relativePath = substr($normalizedPath, strlen($normalizedRoot));
        } else {
            $relativePath = $normalizedPath;
        }
        $relativePath = DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);
        return preg_replace('#/+#', '/', str_replace(DIRECTORY_SEPARATOR, '/', $relativePath));
    }

    public static function isValidUrl($url) {
        return preg_match('#^(https?:)?//#', $url) || str_starts_with($url, 'data:');
    }
}
?>
