<?php
/**
 * Created for plugin-component-translations
 * Datetime: 14.02.2020 17:09
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\I18n;


use Adbar\Dot;
use InvalidArgumentException;
use Leadvertex\Plugin\Components\I18n\Components\Helper;
use RuntimeException;

class Translator
{

    protected static $default;

    protected static $lang;

    public static function config(string $default)
    {
        if (!preg_match('~^[a-z]{2}_[A-Z]{2}$~', $default)) {
            throw new InvalidArgumentException();
        }

        static::$default = $default;
        static::$lang = $default;
    }

    public static function getLanguages(): array
    {
        static::guardNotConfigured();
        return array_unique(
            array_merge(Helper::getLanguages(), [static::$default])
        );
    }

    public static function getLang(): string
    {
        static::guardNotConfigured();
        return static::getLang();
    }

    public static function setLang(string $lang)
    {
        static::guardNotConfigured();
        if (in_array($lang, static::getLanguages())) {
            static::$lang = $lang;
        }
    }

    public static function get(string $category, string $message, array $params = []): string
    {
        static::guardNotConfigured();

        $default = static::load(static::$default);
        $lang = static::load(static::$lang);

        $path = "{$category}.{$message}";
        $translation = $lang->get(
            $path,
            $default->get($path, $message)
        );

        foreach ($params as $key => $param) {
            $translation = str_replace('{' . $key . '}', (string) $param, $translation);
        }

        return $translation;
    }

    protected static function load(string $lang): Dot
    {
        $path = Helper::getTranslationsPath();
        $translations = (string) $path->down($lang . '.json');

        if (file_exists($translations)) {
            $translations = file_get_contents($translations);
            $translations = json_decode($translations, true);

            $associative = [];
            foreach ($translations as $category => $translation) {
                $associative[$category][$translation[0]] = $translation[1];
            }

            return new Dot($translations);
        }

        return new Dot([]);
    }
    
    protected static function guardNotConfigured()
    {
        if (static::$default === null) {
            throw new RuntimeException('Translator was not configured');
        }
    }

}