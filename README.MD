# LeadVertex translations plugin component
`\Leadvertex\Plugin\Components\Translations` - component, which designed to  help with localization of your plugin to desired languages.
It uses a [symfony/console component](https://symfony.com/doc/current/components/console.html "symfony/console component")
for console commands.

## Installation
```shell script
composer require leadvertex/plugin-component-translations
```

## Usage
### Quickstart
This plugin is used to make it easier for you to add localizations to your plugin.

- First of all you need to define default language for your plugin with `Translator::config()` method.\
  The default language is the language in which all the lines in your plugin are written.\
  This lines would be used as `source` to your translations.
  
- To specify the language for translation you need to use `Translator::setLang()` method.

- To get a line of translated text you need to use `Translator::get()` method in which you need to specify `category` and `source`.
  This method also allows you to create lines for translation within your classes,
  commands find all references to this method and create a new translation file with this lines for you.

- To create or update existing translation, you need to use a `LangAddCommand` and `LangUpdateCommand` in your console app.
  `LangAddCommand` will create a new translation file in `translations/` directory. 
  In this file you will see all the categories, the `source` of a lines. You can write a translation of the line to `translated` field.
  

When all these steps are completed, you can use the `Transaltor` component to get any of your translations.
Just set a desired language with `Translator::lang()` method and get translated line with `Translator::get()` method.

### Configuration 
To configure the `Translator` you need to call `config()` and `setLang()` methods and provide default language and set a
translation language.
```php
Translator::config('en_US'); // Setting default/source language as English
Translator::setLang('ru_RU'); // Setting translation language as Russian
```
Note, that you need to use standard locale format (`en_US`, `ru_RU` etc.). 
Also, the `config` method sets the current translation language to the same as default.

You can get current `Translator` default language with `getDefaultLang()` method and a translation language with `getLang()` method.
Also you can get an array of all your existing translation languages with `getLanguages()` method.

### Creating of a new translation 
To set a lines for translation simply use a `Translator::get('Your_category', 'Your_line')` in you classes. All references to `Translator::get()`
will be detected by `LangAddCommand` automatically.\
Note, that you cant use a variables to define category or a source line.\
Simple example:
```php
Translator::get('category', 'Source line 1');
Translator::get('category', 'Source line 2');
```
To create a new translation you need to call a `LangAddCommand` in your console application.
You can do it like this: 
```shell script
console.php lang:add ru_RU
```
Execution of this command will result creating of a new translation file `ru_RU.json` in `translations/` folder.
All translation files has one structure:
```json
{
    "category": [ 
        {
            "source": "Source line 1",
            "translated": "Исходная строка 1"
        },
        {
            "source": "Source line 2",
            "translated": ""
        },
        ...
    ],
    "category 2": [ 
        ...
    ],
}
```
All your lines, that you set in your classes will be generated in this file as `source`'s. To set a translation to the source
you simply need to set translated line to `translated` field. 

### Updating an existing translation
To update translation you need to call a `LangUpdateCommand` in your console application.
You can do it like this:
```shell script
console.php lang:update
```

If command script will find any changes to translations in your classes, old translations will be saved as `.old.json` files.
For example, `ru_RU.json` translation on update will be renamed to `ru_RU.old.json` and an actual translation will be created as a regular `ru_RU.json` file.\
Note, that this command will update all existing translations at once.

### Getting a translated lines
To get a translation of the line you need to use `Translator::get()` method. If you currently do not have a translation for
called line, `Translator::get()` will return the source value.\
Make sure, that you set desired language with `Translator:setLang()` method.\
You can use it like this:
```php
Translator::get('category', 'Source line 1'); // Will return a 'Исходная строка 1' as translation
Translator::get('category', 'Source line 2'); // Translation to this line os not set. Return value will be the source - 'Source line 2'
```

Also you can get all the translation lines with categories as a [adbario/php-dot-notation](https://github.com/adbario/php-dot-notation "adbario/php-dot-notation") object.
To get it you need to use a `load()` method.\
For example, code to get all `ru_RU` lines will be look like this: 
```php
$ruTranslation = Translator::load('ru_RU');
```
