<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Controller\Dto;

use giudicelli\DistributedArchitectureBundle\Entity\MasterCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A DTO respresenting a command.
 *
 * @author Frédéric Giudicelli
 */
class CommandDto
{
    const COMMANDS = [
        MasterCommand::COMMAND_START_ALL,
        MasterCommand::COMMAND_STOP_ALL,
        MasterCommand::COMMAND_START_GROUP,
        MasterCommand::COMMAND_STOP_GROUP,
        MasterCommand::COMMAND_STOP,
    ];

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Choice(choices=CommandDto::COMMANDS)
     */
    private $command;

    private $groupName;

    private $params;

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function setParams(?array $params): self
    {
        $this->params = $params;

        return $this;
    }
}
