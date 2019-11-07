<?php

namespace App\Infrastructure\OpenApi\Command;

use App\Infrastructure\OpenApi\Annotations\Processors as CustomProcessors;
use App\Infrastructure\OpenApi\RelativeFileReader;
use OpenApi\Analyser;
use OpenApi\Processors;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class GenerateOpenApiSpec extends Command
{
    private const NAME = 'paella:openapi:generate';

    private $projectRootDir;

    private $projectDocsDir;

    private $fileReader;

    public function __construct(string $projectRootDir, RelativeFileReader $resourcesFileReader)
    {
        parent::__construct(self::NAME);

        $this->projectRootDir = $projectRootDir;
        $this->projectDocsDir = $projectRootDir . '/docs/openapi';
        $this->fileReader = $resourcesFileReader;
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Generates an OpenApi specification out of the given source code directory, parsing all annotations.')
            ->addArgument('version', InputArgument::REQUIRED, 'API version')
            ->addOption('output-file', null, InputOption::VALUE_REQUIRED, 'Output file name')
            ->addOption('title', null, InputOption::VALUE_REQUIRED, 'API title', 'Paella API Docs')
            ->addOption(
                'groups',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'White list of groups to include in the generated spec.',
                ['public', 'private']
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_dir($this->projectDocsDir)) {
            mkdir($this->projectDocsDir, 0755, true);
        }

        $outputFile = $input->getOption('output-file') ?:
            ($this->projectDocsDir . '/paella-' . $input->getArgument('version') . '-openapi.yaml');

        file_put_contents($outputFile, $this->buildYaml($input));
    }

    protected function buildYaml(InputInterface $input): string
    {
        $sourceDirs = [$this->projectRootDir . '/config', $this->projectRootDir . '/src'];

        Analyser::$whitelist = [
            'OpenApi\Annotations\\',
            'App\Infrastructure\OpenApi\Annotations\\',
            'Symfony\Component\Serializer\Annotation\\',
        ];

        $jsonSpec = \OpenApi\scan($sourceDirs, ['processors' => $this->buildProcessors($input)])
            ->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return Yaml::dump(
            json_decode($jsonSpec),
            10,
            2,
            Yaml::DUMP_OBJECT_AS_MAP ^ Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE ^ Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
    }

    protected function buildProcessorsData(InputInterface $input)
    {
        return [
            'title' => $input->getOption('title'),
            'version' => $input->getArgument('version'),
            'logo' => 'data:image/svg+xml;base64,' . base64_encode($this->fileReader->read('docs/billie-logo.svg')),
            'info_description' => $this->fileReader->read('docs/markdown/index.md'),
        ];
    }

    protected function buildProcessors(InputInterface $input): array
    {
        $data = $this->buildProcessorsData($input);
        $groups = $input->getOption('groups') ?: [];

        if (in_array('full', $groups)) {
            $groups = [];
        }

        return [
            new Processors\MergeIntoOpenApi(),
            new Processors\MergeIntoComponents(),
            new Processors\ImportTraits(),
            new Processors\AugmentSchemas(),
            new Processors\AugmentProperties(),
            new CustomProcessors\AddServers(),
            new CustomProcessors\AugmentMainInfo($data),
            new CustomProcessors\AugmentDescriptions($this->fileReader),
            new Processors\BuildPaths(),
            new Processors\InheritProperties(),
            new Processors\AugmentOperations(),
            new Processors\AugmentParameters(),
            new Processors\MergeJsonContent(),
            new Processors\MergeXmlContent(),
            new Processors\OperationId(),
            new Processors\CleanUnmerged(),
            new CustomProcessors\WhitelistByGroups($groups),
            new CustomProcessors\RemoveOrphanComponents(),
        ];
    }
}
