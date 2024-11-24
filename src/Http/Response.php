<?php

namespace Villeon\Http;

class Response
{
    /**
     * @var string $content
     */
    protected string $content;

    /**
     * @var int $statusCode
     */
    protected int $statusCode;

    /**
     * @var array $headers
     */
    protected array $headers = [];

    /**
     * Response constructor.
     * @param string $content
     * @param int $statusCode
     */
    public function __construct(string $content = '', int $statusCode = 200)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
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
     * @param int $statusCode
     * @return Response
     */
    public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;
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
        // Set status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Output content
        echo $this->content;
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
}
