<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Event;

use giudicelli\DistributedArchitecture\Master\LauncherInterface;
use giudicelli\DistributedArchitecture\Master\ProcessInterface;
use giudicelli\DistributedArchitectureAdminBundle\Entity\GdaMasterCommand;
use giudicelli\DistributedArchitectureAdminBundle\Entity\GdaProcessStatus;
use giudicelli\DistributedArchitectureAdminBundle\Repository\GdaMasterCommandRepository;
use giudicelli\DistributedArchitectureAdminBundle\Repository\GdaProcessStatusRepository;
use giudicelli\DistributedArchitectureBundle\Event\MasterRunningEvent;
use giudicelli\DistributedArchitectureBundle\Event\MasterStartedEvent;
use giudicelli\DistributedArchitectureBundle\Event\MasterStartingEvent;
use giudicelli\DistributedArchitectureBundle\Event\MasterStoppedEvent;
use giudicelli\DistributedArchitectureBundle\Event\ProcessRunningEvent;
use giudicelli\DistributedArchitectureBundle\Event\ProcessStartedEvent;
use giudicelli\DistributedArchitectureBundle\Event\ProcessStoppedEvent;
use giudicelli\DistributedArchitectureBundle\Event\ProcessTimedOutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The Subsciber to the to the distributed_architecture.* events. It will store information in the database.
 *
 * @author Frédéric Giudicelli
 */
final class EventsSubscriber implements EventSubscriberInterface
{
    private $processStatusRepository;
    private $masterCommandRepository;

    public function __construct(GdaProcessStatusRepository $processStatusRepository, GdaMasterCommandRepository $masterCommandRepository)
    {
        $this->processStatusRepository = $processStatusRepository;
        $this->masterCommandRepository = $masterCommandRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            MasterStartingEvent::NAME => [['onMasterStarting', 15]],
            MasterStartedEvent::NAME => [['onMasterStarted', 15]],
            MasterRunningEvent::NAME => [['onMasterRunning', 15]],
            MasterStoppedEvent::NAME => [['onMasterStopped', 15]],
            ProcessStartedEvent::NAME => [['onProcessStarted', 15]],
            ProcessRunningEvent::NAME => [['onProcessRunning', 15]],
            ProcessTimedOutEvent::NAME => [['onProcessTimedOut', 15]],
            ProcessStoppedEvent::NAME => [['onProcessStopped', 15]],
        ];
    }

    public function onMasterStarting(MasterStartingEvent $event): void
    {
        if ($event->getLauncher()->isMaster()) {
            // Clear previous states
            $this->processStatusRepository->deleteAll();
            $this->masterCommandRepository->deleteAll();
        }
    }

    public function onMasterStarted(MasterStartedEvent $event): void
    {
        $masterStatus = $this->getMasterStatus($event->getLauncher());
        $this->processStatusRepository->update($masterStatus);
    }

    public function onMasterRunning(MasterRunningEvent $event): void
    {
        $masterStatus = $this->getMasterStatus($event->getLauncher());
        $masterStatus
            ->setLastSeenAt(new \DateTime())
        ;
        $this->processStatusRepository->update($masterStatus);

        // Only the master launcher takes commands
        if (!$event->getLauncher()->isMaster()) {
            return;
        }

        $masterCommand = $this->masterCommandRepository->findOnePending();
        if (!$masterCommand) {
            return;
        }
        $masterCommand->setStatus(GdaMasterCommand::STATUS_INPROGRESS);
        $this->masterCommandRepository->update($masterCommand);

        switch ($masterCommand->getCommand()) {
            case GdaMasterCommand::COMMAND_START_ALL:
                $event->getLogger()->notice('Handling command '.$masterCommand->getCommand());
                $event->getLauncher()->runAll();

            break;
            case GdaMasterCommand::COMMAND_STOP_ALL:
                $params = $masterCommand->getParams();
                $event->getLogger()->notice('Handling command '.$masterCommand->getCommand().' - force:'.!empty($params['force']));
                $event->getLauncher()->stopAll(!empty($params['force']));

            break;
            case GdaMasterCommand::COMMAND_START_GROUP:
                $event->getLogger()->notice('Handling command '.$masterCommand->getCommand().'::'.$masterCommand->getGroupName());
                $event->getLauncher()->runGroup($masterCommand->getGroupName());

            break;
            case GdaMasterCommand::COMMAND_STOP_GROUP:
                $params = $masterCommand->getParams();
                $event->getLogger()->notice('Handling command '.$masterCommand->getCommand().'::'.$masterCommand->getGroupName().' - force:'.!empty($params['force']));
                $event->getLauncher()->stopGroup($masterCommand->getGroupName(), !empty($params['force']));

            break;
            case GdaMasterCommand::COMMAND_STOP:
                $event->getLogger()->notice('Handling command '.$masterCommand->getCommand());
                $event->getLauncher()->stop();

            break;
        }

        $masterCommand
            ->setStatus(GdaMasterCommand::STATUS_DONE)
            ->setHandledAt(new \DateTime())
        ;
        $this->masterCommandRepository->update($masterCommand);
    }

    public function onMasterStopped(MasterStoppedEvent $event): void
    {
        $masterStatus = $this->getMasterStatus($event->getLauncher());
        $masterStatus
            ->setStoppedAt(new \DateTime())
            ->setStatus('stopped')
        ;
        $this->processStatusRepository->update($masterStatus);
    }

    public function onProcessStarted(ProcessStartedEvent $event): void
    {
        $processStatus = $this->getGdaProcessStatus($event->getProcess());
        $processStatus
            ->setStartedAt(new \DateTime())
            ->setStoppedAt(null)
            ->setOutput(null)
            ->setStatus('started')
        ;

        $this->processStatusRepository->update($processStatus);
    }

    public function onProcessTimedOut(ProcessTimedOutEvent $event): void
    {
        $processStatus = $this->getGdaProcessStatus($event->getProcess());
        $processStatus->setStatus('timedout');

        $this->processStatusRepository->update($processStatus);
    }

    public function onProcessStopped(ProcessStoppedEvent $event): void
    {
        $processStatus = $this->getGdaProcessStatus($event->getProcess());
        $processStatus->setStoppedAt(new \DateTime());
        $processStatus->setStatus('stopped');

        $this->processStatusRepository->update($processStatus);
    }

    public function onProcessRunning(ProcessRunningEvent $event): void
    {
        $processStatus = $this->getGdaProcessStatus($event->getProcess());
        $processStatus->setLastSeenAt(new \DateTime());
        $processStatus->setOutput($event->getLine());

        $this->processStatusRepository->update($processStatus);
    }

    /**
     * Return the GdaProcessStatus entity corresponding to a ProcessInterface process. If it doesn't exist, it is created.
     *
     * @param ProcessInterface $process The process
     *
     * @return GdaProcessStatus The GdaProcessStatus entity
     */
    private function getGdaProcessStatus(ProcessInterface $process): GdaProcessStatus
    {
        $processStatus = $this->processStatusRepository->find($process->getId());
        if (!$processStatus) {
            $processStatus = new GdaProcessStatus();
            $processStatus
                ->setId($process->getId())
                ->setGroupId($process->getGroupId())
                ->setGroupName($process->getGroupConfig()->getName())
                ->setHost(gethostname())
                ->setCommand($process->getDisplay())
            ;
        }

        return $processStatus;
    }

    /**
     * Return the GdaProcessStatus entity corresponding to a LauncherInterface launcher. If it doesn't exist, it is created.
     *
     * @param LauncherInterface $launcher The process
     *
     * @return GdaProcessStatus The GdaProcessStatus entity
     */
    private function getMasterStatus(LauncherInterface $launcher): GdaProcessStatus
    {
        $id = crc32(gethostname().'-'.$launcher->isMaster());

        $processStatus = $this->processStatusRepository->find($id);
        if (!$processStatus) {
            $processStatus = new GdaProcessStatus();
            $processStatus
                ->setId($id)
                ->setGroupId(0)
                ->setGroupName($launcher->isMaster() ? 'gda::master' : 'gda::master::remote')
                ->setHost(gethostname())
                ->setCommand('')
                ->setStartedAt(new \DateTime())
                ->setLastSeenAt(new \DateTime())
                ->setStatus('started')
            ;
        }

        return $processStatus;
    }
}
