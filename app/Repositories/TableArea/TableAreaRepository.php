<?php

namespace App\Repositories\TableArea;

use App\Models\TableArea;
use Illuminate\Support\Facades\DB;

class TableAreaRepository implements TableAreaRepositoryInterface
{
    protected $model;

    public function __construct(TableArea $model)
    {
        $this->model = $model;
    }

    public function getList($params)
    {
        $query = $this->model->query();

        if (isset($params['query'])) {
            $query->where(function ($q) use ($params) {
                $q->where('name', 'like', '%' . $params['query'] . '%')
                    ->orWhere('slug', 'like', '%' . $params['query'] . '%');
            });
        }

        if (isset($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        if (isset($params['slug'])) {
            $query->where('slug', 'like', '%' . $params['slug'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['capacity'])) {
            $query->where('capacity', $params['capacity']);
        }

        return $query->paginate($params['limit'] ?? 10);
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $tableArea = $this->findById($id);
        $tableArea->update($data);
        return $tableArea;
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function getTrashedList($params)
    {
        $query = $this->model->onlyTrashed();

        if (isset($params['query'])) {
            $query->where(function ($q) use ($params) {
                $q->where('name', 'like', '%' . $params['query'] . '%')
                    ->orWhere('slug', 'like', '%' . $params['query'] . '%');
            });
        }

        if (isset($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        if (isset($params['slug'])) {
            $query->where('slug', 'like', '%' . $params['slug'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['capacity'])) {
            $query->where('capacity', $params['capacity']);
        }

        return $query->paginate($params['limit'] ?? 10);
    }

    public function softDelete($id)
    {
        $tableArea = $this->findById($id);
        return $tableArea->delete();
    }

    public function forceDelete($id)
    {
        $tableArea = $this->model->withTrashed()->findOrFail($id);
        return $tableArea->forceDelete();
    }

    public function restore($id)
    {
        $tableArea = $this->model->withTrashed()->findOrFail($id);
        return $tableArea->restore();
    }
}
