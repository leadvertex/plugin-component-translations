<?php

namespace Leadvertex\Plugin\Components\Translations\Commands;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use XAKEPEHOK\Path\Path;

class LangAddCommandTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $filesystem = new Filesystem();
        $translationsPath = (string) (new Path(__DIR__))->up()->up()->down('translations');
        $sourceTranslationsPath = (string) (new Path(__DIR__))->up()->down('SourceFiles')->down('translations');
        $translatorPath = (string) (new Path(__DIR__))->up()->up()->down('src')->down('TranslatorExample.php');

        if ($filesystem->exists($translationsPath)) {
            $filesystem->remove($translationsPath);
        }

        if ($filesystem->exists($translatorPath)) {
            $filesystem->remove($translatorPath);
        }

        $filesystem->copy((string) (new Path(__DIR__))->up()->down('SourceFiles')->down('TranslatorExample.txt'), $translatorPath);
        $filesystem->mirror($sourceTranslationsPath, $translationsPath);
    }

    public function testExecuteCommand()
    {
        $cmdTester = new CommandTester(new LangAddCommand());
        $cmdTester->execute(['lang' => 'fr_FR']);
        $filesystem = new Filesystem();
        $translationPath  = (string) (new Path(__DIR__))->up()->up()->down('translations')->down('fr_FR.json');
        $this->assertTrue($filesystem->exists($translationPath));
        $translation = json_decode(file_get_contents($translationPath), true);

        $this->assertArrayNotHasKey('ignored', $translation);
        foreach ($translation['main'] as $line) {
            $this->assertNotEquals('Ignored message', $line['source']);
        }

    }

    public function testWrongLangFormat()
    {
        $this->expectException(InvalidArgumentException::class);
        $cmdTester = new CommandTester(new LangAddCommand());
        $cmdTester->execute(['lang' => 'Russian']);
    }

    public function testAddAlreadyPresentedLang()
    {
        $this->expectException(RuntimeException::class);
        $cmdTester = new CommandTester(new LangAddCommand());
        $cmdTester->execute(['lang' => 'en_US']);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $filesystem = new Filesystem();
        $translationPath  = (string) (new Path(__DIR__))->up()->up()->down('translations')->down('fr_FR.json');
        if ($filesystem->exists($translationPath)) {
            $filesystem->remove($translationPath);
        }
    }
}