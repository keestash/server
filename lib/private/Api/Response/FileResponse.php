<?php
declare(strict_types=1);

namespace Keestash\Api\Response;

use doganoo\PHPUtil\HTTP\Code;
use KSP\Api\IResponse;

class FileResponse implements IResponse {

    private string $message;
    private int    $code;
    private array  $headers;

    public function __construct(string $message = "", int $code = Code::OK) {
        $this->message = $message;
        $this->code    = $code;
        $this->headers = [];
    }

    public function getCode(): int {
        return $this->code;
    }

    public function getMessage(): ?string {
        return $this->message;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function addHeader(string $name, string $value): void {
        $this->headers[$name] = $value;
    }

}