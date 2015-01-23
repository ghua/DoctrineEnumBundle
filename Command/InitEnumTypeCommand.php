<?php

namespace Fresh\DoctrineEnumBundle\Command;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
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
            ->addOption('dump-sql', null, InputOption::VALUE_NONE, 'Dumps the generated SQL statements to the screen (does not execute them).')
            ->addArgument('name', InputArgument::OPTIONAL, 'The enum type name to initialize');

    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $typeMap = Type::getTypesMap();
        $dumpSqlOption = $input->getOption('dump-sql');
        $typeNameArgument = $input->getArgument('name');

        if ($typeNameArgument) {
            $this->initType($typeNameArgument, $output, $dumpSqlOption);
        } else {
            foreach ($typeMap as $name => $typeClass) {
                $this->initType($name, $output, $dumpSqlOption);
            }
        }

        if (!$dumpSqlOption) {
            $output->writeln('Custom types have been initialized successfully.');
        }
    }

    private function initType($name, OutputInterface $output, $dumpSql = false)
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        $type = Type::getType($name);
        if ($type instanceof AbstractEnumType) {
            if (!$type->requiredInitialization()) {
                return false;
            }

            $statement = $type->getSqlInitialize();
            if ($dumpSql) {
                $output->writeln($statement . ';');
            } else {
                $connection->exec($statement);
            }
        }
    }

}
