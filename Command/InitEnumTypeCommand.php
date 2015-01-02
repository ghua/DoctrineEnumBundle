<?php

namespace Fresh\DoctrineEnumBundle\Command;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Types\Type;

/**
 * Class InitEnumTypeCommand
 *
 * @package Fresh\DoctrineEnumBundle\Command
 *
 * @author  Semyon Velichko <semyon@velichko.net>
 */
class InitEnumTypeCommand extends ContainerAwareCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:type:init')
            ->setDescription('Executes (or dumps) the SQL needed to update the database types to match the current mapping metadata')
            ->addOption('dump-sql', null, InputOption::VALUE_NONE, 'Dumps the generated SQL statements to the screen (does not execute them).');
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        $typeMap = Type::getTypesMap();
        $dumpSqlOption = $input->getOption('dump-sql');

        foreach ($typeMap as $name => $typeClass) {
            $type = Type::getType($name);
            if ($type instanceof AbstractEnumType) {
                if (!$type->requiredInitialization()) {
                    continue;
                }

                $statement = $type->getSqlInitialize();
                if ($dumpSqlOption) {
                    $output->writeln($statement . ';');
                } else {
                    $connection->exec($statement);
                }
            }
        }

        $output->writeln('Custom types have been initialized successfully.');
    }

}
