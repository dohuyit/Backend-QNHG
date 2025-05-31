<?php

namespace App\Domain\Aggregate\Common;

class ListAggregate
{
    public int $total = 0;
    public int $totalPage = 0;
    public int $page = 1;
    public int $perPage = 10;
    public ?string $nextCursor = null;
    public $items;
    private bool $useCursor = false;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function setMeta(int $page, int $perPage, int $total, string $nextCursor = null)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->total = $total;
        $this->totalPage = ceil($total / $perPage);
        $this->nextCursor = $nextCursor;
    }

    public function setMetaCursor(int $perPage, string $nextCursor = null)
    {
        $this->useCursor = true;
        $this->perPage = $perPage;
        $this->nextCursor = $nextCursor;
    }

    public function getResult(): array
    {
        if ($this->useCursor) {
            return [
                'meta' => [
                    'perPage' => $this->perPage,
                    'nextCursor' => $this->nextCursor ? (string)$this->nextCursor : null,
                ],
                'items' => $this->items,
            ];
        }
        return [
            'meta' => [
                'page' => $this->page,
                'perPage' => $this->perPage,
                'total' => $this->total,
                'totalPage' => $this->totalPage,
            ],
            'items' => $this->items,
        ];
    }
}
