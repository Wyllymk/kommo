<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita099b970f9286daedc710e1fe6539d11
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'WyllyMk\\KommoCRM\\Kommo_API' => __DIR__ . '/../..' . '/includes/class-kommo-api.php',
        'WyllyMk\\KommoCRM\\Kommo_CRM' => __DIR__ . '/../..' . '/includes/class-kommo.php',
        'WyllyMk\\KommoCRM\\Logger' => __DIR__ . '/../..' . '/includes/class-logger.php',
        'WyllyMk\\KommoCRM\\Plugin' => __DIR__ . '/../..' . '/includes/class-plugin.php',
        'WyllyMk\\KommoCRM\\Updater' => __DIR__ . '/../..' . '/includes/class-updater.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInita099b970f9286daedc710e1fe6539d11::$classMap;

        }, null, ClassLoader::class);
    }
}
