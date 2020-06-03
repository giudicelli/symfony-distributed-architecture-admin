<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Controller;

use giudicelli\DistributedArchitectureAdminBundle\Controller\Dto\SearchDto;
use giudicelli\DistributedArchitectureAdminBundle\Controller\Http\ApiResponse;
use giudicelli\DistributedArchitectureAdminBundle\Repository\ProcessStatusRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * @IsGranted("ROLE_ADMIN", message="Access denied.").
 */
class ProcessStateController extends AbstractController
{
    public function index()
    {
        return $this->render(
            '@DistributedArchitectureAdmin/ProcessState/index.html.twig',
        );
    }

    public function search(Request $request, ProcessStatusRepository $processStatusRepository)
    {
        $options = [
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
        ];
        // Populate values
        /** @var SearchDto */
        $search = $this->container->get('serializer')->deserialize($request->getContent(), SearchDto::class, 'json', $options);

        return $this->json(
            [
                'processes' => $processStatusRepository->findBySearchRequest($search),
                'total' => $processStatusRepository->countBySearchRequest($search),
            ]
        );
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
