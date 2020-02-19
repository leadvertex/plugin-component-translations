<?php
/**
 * Created for plugin-component-i18n
 * Datetime: 19.02.2020 15:54
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\I18n\Components;


use Composer\Autoload\ClassLoader;
use FilesystemIterator;
use ReflectionClass;
use RuntimeException;
use XAKEPEHOK\Path\Path;

class Helper
{

    public static function getTranslationsPath(): Path
    {
        $reflection = new ReflectionClass(ClassLoader::class);
        $rootPath = (new Path($reflection->getFileName()))->up()->up()->up();
        if (!$rootPath || !is_dir($rootPath)) {
            throw new RuntimeException('Unable to detect vendor path.');
        }
        return $rootPath->down('translations');
    }

    public static function getLanguages(): array
    {
        $iterator = new FilesystemIterator(static::getTranslationsPath());
        $translations = [];
        foreach ($iterator as $info) {
            $basename = $info->getBasename('.' . $info->getExtension());
            if (preg_match('~^[a-z]{2}_[A-Z]{2}$~', $basename)) {
                $translations[] = $basename;
            }
        }
        return $translations;
    }

}