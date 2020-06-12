<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Tests\Event;

use giudicelli\DistributedArchitectureAdminBundle\Entity\GdaMasterCommand;
use giudicelli\DistributedArchitectureAdminBundle\Repository\GdaMasterCommandRepository;
use giudicelli\DistributedArchitectureBundle\Event\MasterRunningEvent;
use giudicelli\DistributedArchitectureBundle\Event\MasterStartedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Frédéric Giudicelli
 */
final class TestEventsSubscriber implements EventSubscriberInterface
{
    private $masterCommandRepository;

    private $stage = 1;

    private $startedTime;

    public function __construct(GdaMasterCommandRepository $masterCommandRepository)
    {
        $this->masterCommandRepository = $masterCommandRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            MasterStartedEvent::NAME => [['onMasterStarted', 15]],
            MasterRunningEvent::NAME => [['onMasterRunning', 15]],
        ];
    }

    public function onMasterStarted(MasterStartedEvent $event): void
    {
        if (!$event->getLauncher()->isMaster()) {
            return;
        }
        $this->startedTime = time();
    }

    public function onMasterRunning(MasterRunningEvent $event): void
    {
        if (!$event->getLauncher()->isMaster()) {
            return;
        }

        if (1 === $this->stage && (time() - $this->startedTime) > 10) {
            $masterCommand = new GdaMasterCommand();
            $masterCommand
                ->setStatus(GdaMasterCommand::STATUS_PENDING)
                ->setCreatedAt(new \DateTime())
                ->setCommand(GdaMasterCommand::COMMAND_STOP_GROUP)
                ->setGroupName('test')
            ;
            $this->masterCommandRepository->update($masterCommand);
            $this->stage = 2;
        } elseif (2 === $this->stage && (time() - $this->startedTime) > 20) {
            $masterCommand = new GdaMasterCommand();
            $masterCommand
                ->setStatus(GdaMasterCommand::STATUS_PENDING)
                ->setCreatedAt(new \DateTime())
                ->setCommand(GdaMasterCommand::COMMAND_START_GROUP)
                ->setGroupName('test')
            ;
            $this->masterCommandRepository->update($masterCommand);
            $this->stage = 3;
        } elseif (3 === $this->stage && (time() - $this->startedTime) > 30) {
            $masterCommand = new GdaMasterCommand();
            $masterCommand
                ->setStatus(GdaMasterCommand::STATUS_PENDING)
                ->setCreatedAt(new \DateTime())
                ->setCommand(GdaMasterCommand::COMMAND_STOP_GROUP)
                ->setGroupName('test 2')
            ;
            $this->masterCommandRepository->update($masterCommand);
            $this->stage = 4;
        } elseif (4 === $this->stage && (time() - $this->startedTime) > 40) {
            $masterCommand = new GdaMasterCommand();
            $masterCommand
                ->setStatus(GdaMasterCommand::STATUS_PENDING)
                ->setCreatedAt(new \DateTime())
                ->setCommand(GdaMasterCommand::COMMAND_START_ALL)
                ->setGroupName('test')
            ;
            $this->masterCommandRepository->update($masterCommand);
            $this->stage = 5;
        } elseif (5 === $this->stage && (time() - $this->startedTime) > 50) {
            $masterCommand = new GdaMasterCommand();
            $masterCommand
                ->setStatus(GdaMasterCommand::STATUS_PENDING)
                ->setCreatedAt(new \DateTime())
                ->setCommand(GdaMasterCommand::COMMAND_STOP)
                ->setGroupName('test')
            ;
            $this->masterCommandRepository->update($masterCommand);
            $this->stage = 6;
        }
    }
}
