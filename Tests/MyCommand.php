<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Tests;

use giudicelli\DistributedArchitecture\Slave\HandlerInterface;
use giudicelli\DistributedArchitectureBundle\Command\AbstractSlaveCommand;

/**
 * @author Frédéric Giudicelli
 */
class MyCommand extends AbstractSlaveCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('da:my-command');
        $this->setDescription('Launch the slave test command');
    }

    protected function runSlave(?HandlerInterface $handler): void
    {
        echo "Child {$handler->getId()} {$handler->getGroupId()} \n";

        while (!$handler->mustStop()) {
            $handler->ping();
            sleep(1);
        }
        echo "Child clean exit\n";
        flush();
    }
}
