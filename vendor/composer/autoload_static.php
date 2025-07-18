<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit329a76e42fb46da43da3980b091cd46c
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Danieltm\\TemplateViewer\\' => 24,
            'Daniel\\Origins\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Danieltm\\TemplateViewer\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Daniel\\Origins\\' => 
        array (
            0 => __DIR__ . '/..' . '/danieltm/origins/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit329a76e42fb46da43da3980b091cd46c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit329a76e42fb46da43da3980b091cd46c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit329a76e42fb46da43da3980b091cd46c::$classMap;

        }, null, ClassLoader::class);
    }
}
