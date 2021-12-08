<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita95a55bde7436f2f6ce01adbc0e00844
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Phpml\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Phpml\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-ai/php-ml/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'N' => 
        array (
            'NlpTools\\' => 
            array (
                0 => __DIR__ . '/..' . '/nlp-tools/nlp-tools/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita95a55bde7436f2f6ce01adbc0e00844::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita95a55bde7436f2f6ce01adbc0e00844::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInita95a55bde7436f2f6ce01adbc0e00844::$prefixesPsr0;
            $loader->classMap = ComposerStaticInita95a55bde7436f2f6ce01adbc0e00844::$classMap;

        }, null, ClassLoader::class);
    }
}
