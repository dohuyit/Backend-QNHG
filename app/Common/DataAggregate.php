<?php

namespace App\Domain\Aggregate\Common;

use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Log;

class DataAggregate
{
    private string $errorCode = NotificationHelper::FAILED;
    private string $message;
    private array $data = [];
    private array $errors = [];

    public function __construct()
    {
        $this->message = __('error.FAILED');
    }

    public function setResultError(string $message = '', array $errors = [], string $errorCode = NotificationHelper::FAILED)
    {
        try {
            $this->errorCode = $errorCode;
            if ($message) {
                $this->message = $message;
            } else {
                $this->message = __("error.$errorCode");
            }
        } catch (\Exception $e) {
            $this->errorCode = NotificationHelper::SERVER_ERROR;
            $this->message = __("error.$errorCode");
            Log::error("[ErrorResultAggregate@setResultError] Error code not exist: ", [$e]);
        }
        if (!empty($errors)) {
            $this->errors = $errors;
        }
    }

    public function setResultSuccess(array $data = [], string $message = '')
    {
        if (!empty($data)) {
            $this->data = $data;
        }
        $this->errorCode = NotificationHelper::SUCCESS;
        if ($message) {
            $this->message = $message;
        } else {
            $this->message = __('error.Thành công');
        }
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function isSuccess(): bool
    {
        return $this->errorCode == NotificationHelper::SUCCESS && empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
