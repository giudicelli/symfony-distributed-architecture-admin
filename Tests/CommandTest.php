<?php

declare(strict_types=1);

namespace giudicelli\DistributedArchitectureAdminBundle\Tests;

use giudicelli\DistributedArchitectureAdminBundle\Entity\GdaProcessStatus;
use giudicelli\DistributedArchitectureAdminBundle\Repository\GdaProcessStatusRepository;
use giudicelli\DistributedArchitectureBundle\Command\MasterCommand;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 * @coversNothing
 *
 * @author Frédéric Giudicelli
 */
final class CommandTest extends KernelTestCase
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var GdaProcessStatusRepository
     */
    private $processStatusRepository;

    public function __construct()
    {
        $this->logger = new Logger();
        parent::__construct();
    }

    protected function setUp(): void
    {
        parent::setUp();

        @unlink(__DIR__.'/var/app.db');

        // Boot up the Symfony Kernel
        static::bootKernel();

        $this->processStatusRepository = new GdaProcessStatusRepository(self::$kernel->getContainer()->get('doctrine'));

        $this->createDatabase();
    }

    /**
     * @before
     */
    public function resetLogger()
    {
        $this->logger->reset();
    }

    public function testKernel()
    {
        $processStatus = new GdaProcessStatus();
        $processStatus
            ->setId(1)
            ->setGroupId(1)
            ->setGroupName('test')
            ->setHost(gethostname())
            ->setStatus('started')
            ->setStartedAt(new \DateTime())
            ->setCommand('yoyo')
        ;
        $this->processStatusRepository->update($processStatus);

        $processStatus = $this->processStatusRepository->find(1);
        $this->assertEquals($processStatus->getStatus(), 'started', 'Is database up and running');
    }

    public function testStatus(): void
    {
        $this->executeCommand($this->logger);
        $output = $this->logger->getOutput();
        sort($output);

        $expected = [
            'debug - [test 2] [127.0.0.1] Connected to host',
            'debug - [test 2] [127.0.0.1] Connected to host',
            'debug - [test 2] [127.0.0.1] Connected to host',
            'debug - [test 2] [127.0.0.1] Connected to host',
            'info - [test 2] [127.0.0.1] [da:my-command/2/1] Child 2 1',
            'info - [test 2] [127.0.0.1] [da:my-command/2/1] Child 2 1',
            'info - [test 2] [127.0.0.1] [da:my-command/2/1] Child clean exit',
            'info - [test 2] [127.0.0.1] [da:my-command/2/1] Child clean exit',
            'info - [test] [localhost] [da:my-command/1/1] Child 1 1',
            'info - [test] [localhost] [da:my-command/1/1] Child 1 1',
            'info - [test] [localhost] [da:my-command/1/1] Child clean exit',
            'info - [test] [localhost] [da:my-command/1/1] Child clean exit',
            'notice -  Handling command start_all',
            'notice -  Handling command start_group::test',
            'notice -  Handling command stop',
            'notice -  Handling command stop_group::test - force:',
            'notice -  Handling command stop_group::test 2 - force:',
            'notice - [master] Stopping...',
            'notice - [test 2] [127.0.0.1] Ended',
            'notice - [test 2] [127.0.0.1] Ended',
            'notice - [test 2] [127.0.0.1] [da:my-command/2/1] Ended',
            'notice - [test 2] [127.0.0.1] [da:my-command/2/1] Ended',
            'notice - [test 2] [127.0.0.1] [master] Received SIGTERM, stopping',
            'notice - [test 2] [127.0.0.1] [master] Received SIGTERM, stopping',
            'notice - [test 2] [127.0.0.1] [master] Stopping...',
            'notice - [test 2] [127.0.0.1] [master] Stopping...',
            'notice - [test] [localhost] [da:my-command/1/1] Ended',
            'notice - [test] [localhost] [da:my-command/1/1] Ended',
        ];
        $this->assertEquals($expected, $output);
    }

    private function createDatabase(): void
    {
        $application = new Application(self::$kernel);
        $tester = new CommandTester($application->get('doctrine:schema:update'));
        $tester->execute(['--force' => true]);
    }

    private function executeCommand(LoggerInterface $logger, $input = [], $options = []): int
    {
        $application = new Application(self::$kernel);
        /** @var MasterCommand */
        $masterCommand = $application->get('distributed_architecture:run-master');
        $masterCommand->setLogger($logger);

        $tester = new CommandTester($masterCommand);

        return $tester->execute($input, $options);
    }
}
