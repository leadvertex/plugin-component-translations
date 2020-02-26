<?php

namespace Leadvertex\Plugin\Components\Translations\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use XAKEPEHOK\Path\Path;

class LangUpdateCommandTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $filesystem = new Filesystem();
        $translationsPath = (string) (new Path(__DIR__))->up()->up()->down('translations');
        $sourceTranslationsPath = (string) (new Path(__DIR__))->up()->down('SourceFiles')->down('translations');
        $translatorPath = (string) (new Path(__DIR__))->up()->up()->down('src')->down('TranslatorUpdateExample.php');

        if ($filesystem->exists($translationsPath)) {
            $filesystem->remove($translationsPath);
        }

        if ($filesystem->exists($translatorPath)) {
            $filesystem->remove($translatorPath);
        }

        $filesystem->copy((string) (new Path(__DIR__))->up()->down('SourceFiles')->down('TranslatorUpdateExample.txt'), $translatorPath);
        $filesystem->mirror($sourceTranslationsPath, $translationsPath);
    }

    public function testExecuteCommand()
    {
        $cmdTester = new CommandTester(new LangUpdateCommand());
        $cmdTester->execute([]);
        $filesystem = new Filesystem();
        $translationsPath  = (string) (new Path(__DIR__))->up()->up()->down('translations');
        $this->assertTrue($filesystem->exists($translationsPath . DIRECTORY_SEPARATOR . 'en_US.json'));
        $this->assertTrue($filesystem->exists($translationsPath . DIRECTORY_SEPARATOR . 'en_US.old.json'));
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $filesystem = new Filesystem();
        $translationsPath = (string) (new Path(__DIR__))->up()->up()->down('translations');
        $sourceTranslationsPath = (string) (new Path(__DIR__))->up()->down('SourceFiles')->down('translations');
        $translatorPath = (string) (new Path(__DIR__))->up()->up()->down('src')->down('TranslatorUpdateExample.php');

        if ($filesystem->exists($translationsPath)) {
            $filesystem->remove($translationsPath);
        }

        if ($filesystem->exists($translatorPath)) {
            $filesystem->remove($translatorPath);
        }

        $filesystem->mirror($sourceTranslationsPath, $translationsPath);
    }
}