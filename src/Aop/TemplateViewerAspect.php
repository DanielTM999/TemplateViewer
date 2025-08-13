<?php

    namespace Danieltm\TemplateViewer\Aop;

    use Daniel\Origins\Annotations\Get;
    use Daniel\Origins\AnnotationsUtils;
    use Daniel\Origins\Aop\Aspect;
    use Daniel\Origins\ModuleManager;
    use Daniel\TemplateViewer\Annotations\AutoRender;
    use Daniel\TemplateViewer\Annotations\MasterPage;
    use Daniel\TemplateViewer\Annotations\MasterPageArguments;
    use Daniel\TemplateViewer\MainFrame;
    use ReflectionMethod;

    final class TemplateViewerAspect extends Aspect
    {
        public function pointCut(object &$controllerEntity, ReflectionMethod &$method, array &$varArgs): bool{
            return AnnotationsUtils::isAnnotationPresent($method, Get::class) && AnnotationsUtils::isAnnotationPresent($method, AutoRender::class);
        }
         
        public function aspectBefore(object &$controllerEntity, ReflectionMethod &$method, array &$varArgs){}

        public function aspectAfter(object &$controllerEntity, ReflectionMethod &$method, array &$varArgs, mixed &$result){
            $methodName = $method->getName();
            $folderName = ucfirst($methodName);
            $title = AnnotationsUtils::getAnnotationArgs($method, AutoRender::class)[0] ?? "Document";
            $module = ModuleManager::getModuleByReference($method);
            $mainFrameName = AnnotationsUtils::getAnnotationArgs($method, MasterPage::class)[0] ?? null;
            MainFrame::setControllerModule($module);
            $returnType = $method->getReturnType();
            $frameArgs = $this->getCustomArgs($method);

            if ($returnType instanceof \ReflectionNamedType) {
                $typeName = $returnType->getName();
                $isNullable = $returnType->allowsNull();

                if ($typeName === 'void') {
                    MainFrame::render($module, $title, "$folderName/$methodName.php", null, $mainFrameName, $frameArgs);
                    return null;
                }

                MainFrame::render($module, $title, "$folderName/$methodName.php", $result,  $mainFrameName, $frameArgs);
                if ($isNullable) {
                    return null;
                } else {
                    return $result;
                }
            }

            MainFrame::render($module, $title, "$folderName/$methodName.php", $result, $mainFrameName, $frameArgs);
            return null;
        }

        private function getCustomArgs(ReflectionMethod &$method): array{
            if(AnnotationsUtils::isAnnotationPresent($method, MasterPageArguments::class)){
                return AnnotationsUtils::getAnnotationArgs($method, MasterPageArguments::class)[0] ?? [];
            }
            return [];
        }

    }
    


?>