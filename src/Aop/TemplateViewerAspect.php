<?php

    namespace Danieltm\TemplateViewer\Aop;

    use Daniel\Origins\Annotations\Get;
    use Daniel\Origins\AnnotationsUtils;
    use Daniel\Origins\Aop\Aspect;
    use Daniel\Origins\ModuleManager;
    use Daniel\TemplateViewer\Annotations\AutoRender;
use Daniel\TemplateViewer\Annotations\MasterPage;
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

            if ($returnType instanceof \ReflectionNamedType) {
                $typeName = $returnType->getName();
                $isNullable = $returnType->allowsNull();

                if ($typeName === 'void') {
                    MainFrame::render($module, $title, "$folderName/$methodName.php", null, $mainFrameName);
                    return null;
                }

                MainFrame::render($module, $title, "$folderName/$methodName.php", $result,  $mainFrameName);
                if ($isNullable) {
                    return null;
                } else {
                    return $result;
                }
            }

            MainFrame::render($module, $title, "$folderName/$methodName.php", $result, $mainFrameName);
            return null;
        }

    }
    


?>