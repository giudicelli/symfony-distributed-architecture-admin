<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use giudicelli\DistributedArchitectureAdminBundle\Entity\GdaMasterCommand;

/**
 * @author Frédéric Giudicelli
 *
 * @method null|GdaMasterCommand find($id, $lockMode = null, $lockVersion = null)
 * @method null|GdaMasterCommand findOneBy(array $criteria, array $orderBy = null)
 * @method GdaMasterCommand[]    findAll()
 * @method GdaMasterCommand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GdaMasterCommandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GdaMasterCommand::class);
    }

    /**
     * Delete all commands.
     */
    public function deleteAll(): void
    {
        $this->createQueryBuilder('mc')
            ->delete()
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Return one pending command.
     *
     * @return null|GdaMasterCommand A pending command or null there is none
     */
    public function findOnePending(): ?GdaMasterCommand
    {
        return $this->createQueryBuilder('mc')
            ->andWhere('mc.status = :status')
            ->setParameter('status', GdaMasterCommand::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Create a command.
     *
     * @param string      $command   The command
     * @param null|string $groupName The optional group name
     * @param null|array  $params    The optional params
     *
     * @return GdaMasterCommand The created command
     */
    public function create(string $command, ?string $groupName, ?array $params): GdaMasterCommand
    {
        /** @var null|GdaMasterCommand */
        $masterCommand = $this->createQueryBuilder('mc')
            ->andWhere('mc.status = :status')
            ->andWhere('mc.command = :command')
            ->andWhere('mc.groupName = :groupName')
            ->setParameter('status', GdaMasterCommand::STATUS_PENDING)
            ->setParameter('groupName', $groupName)
            ->setParameter('command', $command)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if ($masterCommand) {
            $masterCommand->setParams($params);
        } else {
            $masterCommand = new GdaMasterCommand();
            $masterCommand
                ->setCommand($command)
                ->setGroupName($groupName)
                ->setParams($params)
                ->setStatus(GdaMasterCommand::STATUS_PENDING)
                ->setCreatedAt(new \DateTime())
            ;
        }

        $this->getEntityManager()->persist($masterCommand);
        $this->getEntityManager()->flush();

        return $masterCommand;
    }

    /**
     * Update a command.
     *
     * @param GdaMasterCommand $masterCommand The command to update
     */
    public function update(GdaMasterCommand $masterCommand): void
    {
        $this->getEntityManager()->persist($masterCommand);
        $this->getEntityManager()->flush();
    }
}
