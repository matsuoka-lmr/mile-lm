<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Consts\AuthConsts;

class QueryAPI extends BaseAPI
{
    protected $validations = [];

    protected function query(Request $request, $params) {
        $this->error('Not Implemented.', 500);
    }

    protected function getByID(Request $request, $id, $params=[]) {
        $data = $this->query($request, $params)->findOrFail($id);
        \Illuminate\Support\Facades\Log::debug('QueryAPI@getByID method response:', ['data' => $data]);
        if (empty($data)) $this->error('Not Found.', 404);
        return $data;
    }

    protected $listRoles = false;
    protected $listValidations = null;
    protected $filters = [];
    protected $orders = [];

    protected function listQuery(Request $request, $params) {
        $query = $this->query($request, $params);
        foreach ($this->filters as $key => $op) {
            $val = $request->input("search.$key", null);
            if (isset($val)) {
                if (is_callable($op)) $op($query, $val);
                else $query->where($key, $op, $op=='like' ? '%'.addcslashes($val, '%_\\').'%' : $val);
            }
        }

        $sort = $request->input('sort', null);
        if (is_array($sort)) {
            foreach ($sort as $sortBy) {
                $query->orderBy($sortBy['key'], $sortBy['order']);
            }
        }

        foreach ($this->orders as $by => $desc) {
            $query->orderBy($by, $desc ? 'desc' : 'asc');
        }

        // Log::debug($query->toSql(), $query->getBindings());
        // Log::debug(json_encode($query->toMql()), $query->getBindings());
        return $query;
    }

    protected function getListRoles() {
        return $this->listRoles != false ? $this->listRoles : $this->roles;
    }

    protected function stream($callback) {
        $response = new StreamedResponse($callback, 200, $headers);
        if (! is_null($name)) {
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                $disposition,
                $name,
                $this->fallbackName($name)
            ));
        }
        return $response;
    }

    public function list(Request $request) {
        Log::info('QueryAPI@list called for path: ' . $request->path());
        Log::info('User details:', ['user' => $this->user]);
        $this->auth($request, $this->getListRoles());
        $params = is_array($this->listValidations) ? $this->validate($request, $this->listValidations) : [];
        $list = $this->listQuery($request, $params);
        if ($request->has('page')) {
            $perPage = $request->has('perpage') ? $request->input('perpage', 10) : 10;
            $paginator = $list->paginate($perPage);
            if ($paginator->isEmpty() && $paginator->total()) {
                $paginator = $list->paginate($perPage, ['*'], 'page', $paginator->lastPage());
            }
            return new JsonResponse([
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'page' => $paginator->currentPage(),
                'total' => $paginator->total(),
                'data' => $paginator->items(),
            ]);
        } else {
            $callback = function() use (&$list) {
                \ob_flush();
                echo '{"data":[';
                $count = 0;
                foreach ($list->cursor() as $item) {
                    \Illuminate\Support\Facades\Log::debug('QueryAPI@list cursor item:', ['item' => $item]);
                    if ($count++ > 0) {
                        echo ',';
                        if ($count % 100 == 0) \ob_flush();
                    }
                    echo \json_encode($item);
                }
                echo ']}';
            };
            return new StreamedResponse($callback, 200, ['Content-Type'=> 'application/json']);
        }
    }

    public function get(Request $request, $id) {
        $this->auth($request);
        return new JsonResponse($this->getByID($request, $id, array_slice(func_get_args(), 2)));
    }

    protected $editRoles = false;

    protected function getEditRoles() {
        return $this->editRoles != false ? $this->editRoles : $this->roles;
    }

    protected function setData($data, $params, $request) {
        $data->fill($params);
        return $data;
    }

    protected function saveUpdatedData($data, $params) {
        return $data->save();
    }

    protected function additonalUpdateValidate($request, $id, &$params) {
        return false;
    }

    protected function updateValidations($request, $id) {
        return $this->validations;
    }

    public function update(Request $request, $id) {
        $this->auth($request, $this->getEditRoles());
        $params = $this->validate($request, $this->updateValidations($request, $id));
        if ($errors = $this->additonalUpdateValidate($request, $id, $params)) $this->buildFailedValidationResponse($request, $erros);
        $data = $this->getByID($request, $id, $params);
        $data = $this->setData($data, $params, $request);
        return [
            'success' => $this->saveUpdatedData($data, $params)
        ];
    }

    protected function newData($request, $params) {
        $this->error('Not Implemented.', 500);
    }

    protected function saveCreatedData($data, $params) {
        return $this->saveUpdatedData($data, $params);
    }

    protected function additonalCreateValidate($request, &$params) {
        return false;
    }

    protected function createValidations($request) {
        return $this->validations;
    }

    public function create(Request $request) {
        $this->auth($request, $this->getEditRoles());
        $params = $this->validate($request, $this->createValidations($request));
        if ($errors = $this->additonalCreateValidate($request, $params)) $this->buildFailedValidationResponse($request, $erros);
        $data = $this->newData($request, $params);
        $data = $this->setData($data, $params, $request);
        return [
            'success' => $this->saveCreatedData($data, $params)
        ];
    }

    protected function saveDeletedData($data) {
        return $data->delete();
    }

    public function delete(Request $request, $id) {
        $this->auth($request, $this->getEditRoles());
        $data = $this->getByID($request, $id, array_slice(func_get_args(), 2));
        return [
            'success' => $this->saveDeletedData($data)
        ];
    }
}
