<?php

    namespace Daniel\TemplateViewer\Annotations;

    use Attribute;

    #[Attribute(Attribute::TARGET_METHOD)]
    final class MasterPage
    {
        public function __construct(string $title = "MainFrame") {}
    }

?>
    

