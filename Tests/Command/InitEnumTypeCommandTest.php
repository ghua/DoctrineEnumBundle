<?php


namespace Fresh\DoctrineEnumBundle\Tests\Command;

use Fresh\DoctrineEnumBundle\Command\InitEnumTypeCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class InitEnumTypeCommandTest
 *
 * @package Fresh\DoctrineEnumBundle\Tests\Command
 *
 * @author  Semyon Velichko <semyon@velichko.net>
 */
class InitEnumTypeCommandTest extends KernelTestCase
{

    public function testExecute()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new InitEnumTypeCommand());

        $command = $application->find('doctrine:type:init');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('command' => $command->getName(), '--dump-sql' => true)
        );

        $display = $commandTester->getDisplay();

        $this->assertContains("CREATE TYPE BasketballPositionType AS ENUM ('PG', 'SG', 'SF', 'PF', 'C');", $display);
    }

}