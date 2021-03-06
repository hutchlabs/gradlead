<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit05476358c0c9fb589ed96f00c6e013eb
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'L' => 
        array (
            'Leafo\\ScssPhp\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Leafo\\ScssPhp\\' => 
        array (
            0 => __DIR__ . '/..' . '/leafo/scssphp/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'R' => 
        array (
            'Raven_' => 
            array (
                0 => __DIR__ . '/..' . '/sentry/sentry/lib',
            ),
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 
            array (
                0 => __DIR__ . '/..' . '/psr/log',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit05476358c0c9fb589ed96f00c6e013eb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit05476358c0c9fb589ed96f00c6e013eb::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit05476358c0c9fb589ed96f00c6e013eb::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
