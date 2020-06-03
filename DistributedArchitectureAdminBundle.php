<?php

namespace giudicelli\DistributedArchitectureAdminBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Frédéric Giudicelli
 */
class DistributedArchitectureAdminBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        // noop
    }
}
