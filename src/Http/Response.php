<?php

namespace Villeon\Http;

class Response
{
    private const DEFAULT_STATUS_CODE = 200;
    /**
     * @var string $content
     */
    protected string $content;

    /**
     * @var int $statusCode
     */
    protected int $statusCode = 200;

    /**
     * @var array $headers
     */
    protected array $headers = [];

    /**
     * @var string | null $location
     */
    private ?string $location;

    /**
     * Response constructor.
     * @param string $content
     * @param int $statusCode
     * @param Response|null $response
     */
    public function __construct(string $content = '', int $statusCode = 200, ?Response $response = null)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->location = null;
        if ($response)
            $this->clone($response);
    }

    private function clone(Response $response): void
    {
        $this->statusCode = $response->statusCode;
        $this->location = $response->location;
        $this->content = $response->content;
        $this->headers = $response->headers;
    }

    /**
     * Set the content of the response.
     * @param string $content
     * @return Response
     */
    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Get the content of the response.
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set the HTTP status code.
     * @param int|null $statusCode
     * @return Response
     */
    public function setStatusCode(?int $statusCode): static
    {
        $this->statusCode = $statusCode ?? self::DEFAULT_STATUS_CODE;
        return $this;
    }

    /**
     * Get the HTTP status code.
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set a header for the response.
     * @param string $name
     * @param string $value
     * @return Response
     */
    public function setHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Send the response to the client (outputs the content).
     * @return void
     */
    public function send(): void
    {

        http_response_code($this->statusCode);


        print_r($this->content);
    }

    /**
     * Send a JSON response.
     * @param mixed $data
     * @return Response
     */
    public function sendJson(mixed $data): static
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        return $this;
    }

    public function __toString(): string
    {
        return "";
    }

    public static function from(Response $response): Response
    {
        return new Response(response: $response);
    }

    /**
     * @return array
     */
    public function resolved(): array
    {
        return [
            "headers" => $this->headers,
            "code" => $this->statusCode,
            "content" => $this->content,
            "location" => $this->location
        ];
    }
}
