<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Tests;

use giudicelli\DistributedArchitectureBundle\Command\AbstractSlaveCommand;
use giudicelli\DistributedArchitectureBundle\Handler;
use Psr\Log\LoggerInterface;

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

    protected function runSlave(?Handler $handler, ?LoggerInterface $logger): void
    {
        $groupConfig = $handler->getGroupConfig();
        echo "Child {$handler->getId()} {$handler->getGroupId()} \n";

        while (!$handler->mustStop()) {
            $handler->ping();
            sleep(1);
        }
        echo "Child clean exit\n";
        flush();
    }
}
