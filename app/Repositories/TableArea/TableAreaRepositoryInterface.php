<?php

namespace App\Repositories\TableArea;

interface TableAreaRepositoryInterface
{
    public function getList($params);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getTrashedList($params);
    public function softDelete($id);
    public function forceDelete($id);
    public function restore($id);
}
