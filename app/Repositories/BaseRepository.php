<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;
use App\Http\Responses\PKResponseResource;
use App\Http\Responses\PKResponseResourceCreateError;
use App\Http\Responses\PKResponseResourceCreateSuccessful;
use App\Http\Responses\PKResponseResourceDeleteSuccessful;
use App\Http\Responses\PKResponseResourceNotFound;
use App\Http\Responses\PKResponseResourceUpdateError;
use App\Http\Responses\PKResponseResourceUpdateMassAssignmentError;
use App\Http\Responses\PKResponseResourceUpdateSuccessful;
use Ramsey\Uuid\Uuid;

abstract class BaseRepository
{
    /** @var Model */
    protected $model;

    function __construct (Model $model)
    {
        $this->model = $model;
    }

    public function all($columns = array('*'))
    {
        return $this->model->all($columns);
    }

    public function paginate($perPage = 20, $columns = array('*'))
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @param array $data
     * @return PKResponseResourceCreateSuccessful|PKResponseResourceCreateError
     */
    public function create(array $data)
    {
        $resource = new $this->model;
        $resource->fill($data);
        $resource->guid = Uuid::uuid4()->toString();
        if(!$resource->save()){
            return new PKResponseResourceCreateError;
        }
        return new PKResponseResourceCreateSuccessful($resource);
    }


    /**
     * @param array $data
     * @param $id
     * @return PKResponseResourceNotFound|PKResponseResourceUpdateError|PKResponseResourceUpdateSuccessful
     */
    public function update(array $data, $id)
    {
        $resource = $this->model->whereId($id)->first();
        if(!$resource){
            return new PKResponseResourceNotFound;
        }
        try{
            $success = $resource->update($data);
        }
        catch(MassAssignmentException $e){
            return new PKResponseResourceUpdateMassAssignmentError;
        }
        if(!$success){
            return new PKResponseResourceUpdateError;
        }
        return new PKResponseResourceUpdateSuccessful($resource);
    }


    public function delete($id)
    {
        $resource = $this->model->whereId($id)->first();
        if(!$resource){
            return new PKResponseResourceNotFound;
        }
        if(!$resource->delete($id)){
            return new PKResponseResourceDeleteError;
        }
        return new PKResponseResourceDeleteSuccessful;
    }

    public function find($id, $columns = array('*'))
    {
        $resource = $this->model->find($id, $columns);
        if(!$resource){
            return new PKResponseResourceNotFound;
        }
        return new PKResponseResource($resource);
    }

    public function findBy($attribute, $value, $columns = array('*'))
    {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

}