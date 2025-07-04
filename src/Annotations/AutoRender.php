<?php

    namespace Daniel\TemplateViewer\Annotations;

    use Attribute;

    #[Attribute(Attribute::TARGET_METHOD)]
    final class AutoRender
    {
        public function __construct(string $title = "Document") {}
    }

?>
    

