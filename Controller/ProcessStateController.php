<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Controller;

use giudicelli\DistributedArchitectureAdminBundle\Controller\Dto\CommandDto;
use giudicelli\DistributedArchitectureAdminBundle\Controller\Dto\SearchDto;
use giudicelli\DistributedArchitectureAdminBundle\Controller\Http\ApiResponse;
use giudicelli\DistributedArchitectureAdminBundle\Repository\GdaMasterCommandRepository;
use giudicelli\DistributedArchitectureAdminBundle\Repository\GdaProcessStatusRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * The controller to administer the master process.
 *
 * @author Frédéric Giudicelli
 *
 * @IsGranted("ROLE_ADMIN", message="Access denied.").
 */
class ProcessStateController extends AbstractController
{
    private $validator;

    private $processStatusRepository;

    private $masterCommandRepository;

    public function __construct(
        ValidatorInterface $validator,
        GdaProcessStatusRepository $processStatusRepository,
        GdaMasterCommandRepository $masterCommandRepository
    ) {
        $this->validator = $validator;
        $this->processStatusRepository = $processStatusRepository;
        $this->masterCommandRepository = $masterCommandRepository;
    }

    public function index()
    {
        return $this->render(
            '@DistributedArchitectureAdmin/ProcessState/index.html.twig',
        );
    }

    public function search(Request $request)
    {
        $options = [
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
        ];
        // Populate values
        /** @var SearchDto */
        $search = $this->container->get('serializer')->deserialize($request->getContent(), SearchDto::class, 'json', $options);

        return $this->json(
            $this->processStatusRepository->findBySearchRequest($search)
        );
    }

    public function command(Request $request)
    {
        $options = [
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
        ];
        // Populate values
        /** @var CommandDto */
        $command = $this->container->get('serializer')->deserialize($request->getContent(), CommandDto::class, 'json', $options);
        $violations = $this->validator->validate($command);
        if (count($violations)) {
            throw new ValidatorException($violations);
        }

        $this->masterCommandRepository->create(
            $command->getCommand(),
            $command->getGroupName(),
            $command->getParams()
        );

        return $this->json([]);
    }

    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        return $this->jsonErrors('', $data, [], $status, $headers, $context);
    }

    protected function jsonMessage($message, $data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        return $this->jsonErrors($message, $data, [], $status, $headers, $context);
    }

    protected function jsonErrors($message, $data, array $errors = [], int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        if ($this->container->has('serializer')) {
            $json = $this->container->get('serializer')->serialize($data, 'json', array_merge([
                'json_encode_options' => ApiResponse::DEFAULT_ENCODING_OPTIONS,
            ], $context));

            return new ApiResponse($message, $json, $errors, $status, $headers, true);
        }

        return new ApiResponse($message, $data, $errors, $status, $headers);
    }
}
