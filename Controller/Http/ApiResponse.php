<?php

namespace giudicelli\DistributedArchitectureAdminBundle\Controller\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * An API json response.
 *
 * @author Frédéric Giudicelli
 */
class ApiResponse extends JsonResponse
{
    /**
     * ApiResponse constructor.
     *
     * @param mixed $data
     */
    public function __construct(string $message, $data = null, array $errors = [], int $status = 200, array $headers = [], bool $json = false)
    {
        parent::__construct($this->format($message, $data, $errors, $json), $status, $headers, true);
    }

    /**
     * Format the API response.
     *
     * @param string $message A message
     * @param mixed  $data    The actual data to send back
     * @param array  $errors  Some errors
     * @param bool   $json    Is data already json encoded?
     *
     * @return string The json encoded response
     */
    private function format(string $message, $data = null, array $errors = [], bool $json)
    {
        if (null === $data) {
            $data = new \ArrayObject();
        }
        $response = [
            'message' => $message,
            'data' => $json ? json_decode($data) : $data,
        ];
        if ($errors) {
            $response['errors'] = $errors;
        }

        return json_encode($response);
    }
}
