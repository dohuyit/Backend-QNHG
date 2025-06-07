<?php

namespace App\Common;

use App\Helpers\ErrorHelper;
use Illuminate\Support\Facades\Log;

class DataAggregate
{
    private string $code;

    private string $message;

    private array $data = [];

    private array $errors = [];

    public function __construct(string $code = ErrorHelper::FAILED, string $message = '')
    {
        $this->code = $code;
        $this->message = $message ?: __("error.$code");
    }

    public function setResultSuccess(array $data = [], string $message = ''): void
    {
        $this->code = ErrorHelper::SUCCESS;
        $this->message = $message ?: __('error.SUCCESS');
        $this->data = $data;
        $this->errors = [];
    }

    public function setResultError(string $message = '', array $errors = [], string $code = ErrorHelper::FAILED): void
    {
        try {
            $this->code = $code;
            $this->message = $message ?: __("error.$code");
        } catch (\Exception $e) {
            $this->code = ErrorHelper::SERVER_ERROR;
            $this->message = __('error.'.ErrorHelper::SERVER_ERROR);
            Log::error('[ResultAggregate@setResultError] Error code not exist: ', [$e]);
        }

        if (! empty($errors)) {
            $this->errors = $errors;
        }
    }

    public function isSuccessCode(): bool
    {
        return $this->code === ErrorHelper::SUCCESS && empty($this->errors);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
