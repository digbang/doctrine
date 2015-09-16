<?php namespace Digbang\Doctrine\Commands\Mappings;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Show information about mapped entities.
 *
 * @link    www.doctrine-project.org
 * @since   2.4
 * @author  Daniel Leech <daniel@dantleech.com>
 */
final class DescribeCommand extends Command
{
    protected $name = 'doctrine:mappings:describe';

    protected $description = 'Display information about mapped objects';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setHelp(<<<EOT
The %command.full_name% command describes the metadata for the given full or partial entity class name.

    <info>%command.full_name%</info> My\Namespace\Entity\MyEntity

Or:

    <info>%command.full_name%</info> MyEntity
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EntityManagerInterface $em)
    {
        $this->displayEntity($this->argument('entityName'), $em, $this->output);

        return 0;
    }

    /**
     * Display all the mapping information for a single Entity.
     *
     * @param string                 $entityName    Full or partial entity class name
     * @param EntityManagerInterface $entityManager
     * @param OutputInterface        $output
     */
    private function displayEntity($entityName, EntityManagerInterface $entityManager, OutputInterface $output)
    {
        $table = new Table($output);

        $table->setHeaders(['Field', 'Value']);

        $metadata = $this->getClassMetadata($entityName, $entityManager);

        array_map(
            [$table, 'addRow'],
            array_merge(
                [
                    $this->formatField('Name', $metadata->name),
                    $this->formatField('Root entity name', $metadata->rootEntityName),
                    $this->formatField('Custom generator definition', $metadata->customGeneratorDefinition),
                    $this->formatField('Custom repository class', $metadata->customRepositoryClassName),
                    $this->formatField('Mapped super class?', $metadata->isMappedSuperclass),
                    $this->formatField('Embedded class?', $metadata->isEmbeddedClass),
                    $this->formatField('Parent classes', $metadata->parentClasses),
                    $this->formatField('Sub classes', $metadata->subClasses),
                    $this->formatField('Embedded classes', $metadata->subClasses),
                    $this->formatField('Named queries', $metadata->namedQueries),
                    $this->formatField('Named native queries', $metadata->namedNativeQueries),
                    $this->formatField('SQL result set mappings', $metadata->sqlResultSetMappings),
                    $this->formatField('Identifier', $metadata->identifier),
                    $this->formatField('Inheritance type', $metadata->inheritanceType),
                    $this->formatField('Discriminator column', $metadata->discriminatorColumn),
                    $this->formatField('Discriminator value', $metadata->discriminatorValue),
                    $this->formatField('Discriminator map', $metadata->discriminatorMap),
                    $this->formatField('Generator type', $metadata->generatorType),
                    $this->formatField('Table', $metadata->table),
                    $this->formatField('Composite identifier?', $metadata->isIdentifierComposite),
                    $this->formatField('Foreign identifier?', $metadata->containsForeignIdentifier),
                    $this->formatField('Sequence generator definition', $metadata->sequenceGeneratorDefinition),
                    $this->formatField('Table generator definition', $metadata->tableGeneratorDefinition),
                    $this->formatField('Change tracking policy', $metadata->changeTrackingPolicy),
                    $this->formatField('Versioned?', $metadata->isVersioned),
                    $this->formatField('Version field', $metadata->versionField),
                    $this->formatField('Read only?', $metadata->isReadOnly),

                    $this->formatEntityListeners($metadata->entityListeners),
                ],
                [$this->formatField('Association mappings:', '')],
                $this->formatMappings($metadata->associationMappings),
                [$this->formatField('Field mappings:', '')],
                $this->formatMappings($metadata->fieldMappings)
            )
        );

        $table->render();
    }

    /**
     * Return all mapped entity class names
     *
     * @param EntityManagerInterface $entityManager
     *
     * @return string[]
     */
    private function getMappedEntities(EntityManagerInterface $entityManager)
    {
        $entityClassNames = $entityManager
            ->getConfiguration()
            ->getMetadataDriverImpl()
            ->getAllClassNames();

        if ( ! $entityClassNames) {
            throw new \InvalidArgumentException(
                'You do not have any mapped Doctrine ORM entities according to the current configuration. '.
                'If you have entities or mapping files you should check your mapping configuration for errors.'
            );
        }

        return $entityClassNames;
    }

    /**
     * Return the class metadata for the given entity
     * name
     *
     * @param string                 $entityName    Full or partial entity name
     * @param EntityManagerInterface $entityManager
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    private function getClassMetadata($entityName, EntityManagerInterface $entityManager)
    {
        try {
            return $entityManager->getClassMetadata($entityName);
        } catch (MappingException $e) {
        }

        $matches = array_filter(
            $this->getMappedEntities($entityManager),
            function ($mappedEntity) use ($entityName) {
                return preg_match('{' . preg_quote($entityName) . '}', $mappedEntity);
            }
        );

        if ( ! $matches) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find any mapped Entity classes matching "%s"',
                $entityName
            ));
        }

        if (count($matches) > 1) {
            throw new \InvalidArgumentException(sprintf(
                'Entity name "%s" is ambigous, possible matches: "%s"',
                $entityName, implode(', ', $matches)
            ));
        }

        return $entityManager->getClassMetadata(current($matches));
    }

    /**
     * Format the given value for console output
     *
     * @param mixed $value
     *
     * @return string
     */
    private function formatValue($value)
    {
        if ('' === $value) {
            return '';
        }

        if (null === $value) {
            return '<comment>Null</comment>';
        }

        if (is_bool($value)) {
            return '<comment>' . ($value ? 'True' : 'False') . '</comment>';
        }

        if (empty($value)) {
            return '<comment>Empty</comment>';
        }

        if (is_array($value)) {
            if (defined('JSON_UNESCAPED_UNICODE') && defined('JSON_UNESCAPED_SLASHES')) {
                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            return json_encode($value);
        }

        if (is_object($value)) {
            return sprintf('<%s>', get_class($value));
        }

        if (is_scalar($value)) {
            return $value;
        }

        throw new \InvalidArgumentException(sprintf('Do not know how to format value "%s"', print_r($value, true)));
    }

    /**
     * Add the given label and value to the two column table output
     *
     * @param string $label Label for the value
     * @param mixed  $value A Value to show
     *
     * @return array
     */
    private function formatField($label, $value)
    {
        if (null === $value) {
            $value = '<comment>None</comment>';
        }

        return [sprintf('<info>%s</info>', $label), $this->formatValue($value)];
    }

    /**
     * Format the association mappings
     *
     * @param array
     *
     * @return array
     */
    private function formatMappings(array $propertyMappings)
    {
        $output = [];

        foreach ($propertyMappings as $propertyName => $mapping) {
            $output[] = $this->formatField(sprintf('  %s', $propertyName), '');

            foreach ($mapping as $field => $value) {
                $output[] = $this->formatField(sprintf('    %s', $field), $this->formatValue($value));
            }
        }

        return $output;
    }

    /**
     * Format the entity listeners
     *
     * @param array $entityListeners
     *
     * @return array
     */
    private function formatEntityListeners(array $entityListeners)
    {
        return $this->formatField(
            'Entity listeners',
            array_map(
                function ($entityListener) {
                    return get_class($entityListener);
                },
                $entityListeners
            )
        );
    }

    protected function getArguments()
    {
        return [
            ['entityName', InputArgument::REQUIRED, 'Full or partial name of entity']
        ];
    }
}
