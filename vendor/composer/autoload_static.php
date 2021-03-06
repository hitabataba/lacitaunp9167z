<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1064d8017a6b8c68ac44a9c2c2b5fa87
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LINE\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LINE\\' => 
        array (
            0 => __DIR__ . '/..' . '/linecorp/line-bot-sdk/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1064d8017a6b8c68ac44a9c2c2b5fa87::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1064d8017a6b8c68ac44a9c2c2b5fa87::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
