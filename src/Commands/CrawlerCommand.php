<?php
/**
 * Created for plugin-component-translations
 * Datetime: 17.02.2020 18:59
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Translations\Commands;


use Adbar\Dot;
use HaydenPierce\ClassFinder\ClassFinder;
use Leadvertex\Plugin\Components\Translations\Components\Helper;
use PhpParser\ParserFactory;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use XAKEPEHOK\Path\Path;

abstract class CrawlerCommand extends Command
{

    /** @var Path */
    protected $translationFilesPath;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->translationFilesPath = Helper::getTranslationsPath();
        if (!is_dir((string) $this->translationFilesPath)) {
            mkdir((string) $this->translationFilesPath, 0775, true);
        }
    }

    protected function asJson(array $data): string
    {
        return json_encode($this->schemeToExport($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    protected function schemeToExport(array $scheme): array
    {
        $export = [];
        foreach ($scheme as $category => $translations) {
            foreach ($translations as $translation => $value) {
                $export[$category][] = [
                    'source' => $translation,
                    'translated' => is_bool($value) ? '' : $value,
                ];
            }
        }
        return $export;
    }

    protected function crawl(): array
    {
        ClassFinder::enablePSR4Support();
        $classes = ClassFinder::getClassesInNamespace('Leadvertex\Plugin', ClassFinder::RECURSIVE_MODE);

        $scheme = [];
        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $file = $reflection->getFileName();

            $code = file_get_contents($file);
            $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
            $stmts = json_decode(json_encode($parser->parse($code)), true);
            $result = $this->recursion($stmts);


            foreach ($result as $value) {
                $scheme[$value[0]][$value[1]] = true;
            }
        }


        return $scheme;
    }

    private function recursion(array $statements): array
    {
        $result = [];
        foreach ($statements as $statement) {

            if (!is_array($statement)) {
                continue;
            }

            $node = new Dot($statement);
            if ($node->has('expr')) {
                if ($node->get('expr.nodeType') === 'Expr_StaticCall') {
                    if (
                        $node->get('expr.class.nodeType') === 'Name' &&
                        $node->get('expr.class.parts.0') === 'Translator' &&
                        $node->get('expr.name.nodeType') === 'Identifier' &&
                        $node->get('expr.name.name') === 'get'
                    ) {
                        if (
                            $node->get('expr.args.0.value.nodeType') === 'Scalar_String' &&
                            $node->get('expr.args.1.value.nodeType') === 'Scalar_String'
                        ) {
                            $result[] = [
                                $node->get('expr.args.0.value.value'),
                                $node->get('expr.args.1.value.value'),
                            ];
                            continue;
                        }
                    }
                }
            }

            $result = array_merge($result, $this->recursion($statement));

        }

        return $result;
    }

}