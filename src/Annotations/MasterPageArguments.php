<?php

    namespace Daniel\TemplateViewer\Annotations;

    use Attribute;

    #[Attribute(Attribute::TARGET_METHOD)]
    final class MasterPageArguments
    {
        public function __construct(array $title = []) {}
    }

?>
    

