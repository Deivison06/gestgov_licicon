<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Falha tipada na comunicação com a OpenAI.
 *
 * O `code` é semântico (não é HTTP) e é mapeado pelo Controller para
 * status HTTP + mensagem amigável ao usuário.
 */
class OpenAiServiceException extends RuntimeException
{
    public const TIMEOUT     = 'TIMEOUT';
    public const RATE_LIMIT  = 'RATE_LIMIT';
    public const INVALID_KEY = 'INVALID_KEY';
    public const CONTENT_FILTER = 'CONTENT_FILTER';
    public const EMPTY_RESPONSE = 'EMPTY_RESPONSE';
    public const NETWORK     = 'NETWORK';
    public const UNKNOWN     = 'UNKNOWN';

    public function __construct(string $codeSemantico, string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message ?: $codeSemantico, 0, $previous);
        $this->codeSemantico = $codeSemantico;
    }

    private string $codeSemantico;

    public function getCodeSemantico(): string
    {
        return $this->codeSemantico;
    }
}
