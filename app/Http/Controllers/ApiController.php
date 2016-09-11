<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use App\Http\ApiResponseManager;
use App\Repositories\BaseRepository;
use App\Http\Responses\PKResponseResourceNotFound;
use App\Http\Responses\PKResponseResourceCreateError;
use App\Http\Responses\PKResponseResourceCreateSuccessful;
use App\Http\Responses\PKResponseResourceDeleteSuccessful;
use App\Http\Responses\PKResponseResourceUpdateSuccessful;
use App\Http\Responses\PKResponseResourceUpdateMassAssignmentError;


class ApiController extends Controller {

    /* @var Manager Manager */
    protected $fractalManager;


    /** @var BaseRepository */
    protected $queryRepository;


    /** @var AbstractTransformer Transformer */
    protected $transformer;


    /**
     * A variable to store custom form request classes for children.
     * @var array */
    protected $requestClasses = [];

    /** @var ApiResponseManager */
    protected $apiManager;

    function __construct()
    {
        $this->fractalManager = App::make(Manager::class);
        $this->apiManager = App::make(ApiResponseManager::class);
    }

    /**
     * This checks to see if there is a Custom Form Request set in the child controller
     * If so, then use that custom form request and instantiate it so that it can be
     * accessed. If not, then a default class is created. The request is returned
     *
     * @return mixed
     */
    protected function initialiseRequest()
    {
        // Find out the method before that called the getRequest method,
        // Whether it was the store or update method.
        $methodName = debug_backtrace()[1]['function'];
        // The custom form request classes set in the child controller classes
        // and see if any classes have been set for the method. If not then
        // use the default Illuminate\request class to capture the data.
        $requestClasses = $this->requestClasses;
        $class = array_get($requestClasses, $methodName, Request::class);
        // Instantiate the request class
        return App::make($class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->initialiseRequest();
        $items = $this->queryRepository->all();
        if (!$items) {
            return $this->apiManager->respondNotFound('Items do not exist.');
        }
        $itemsResource = new Collection($items, $this->transformer);
        $processedItems = $this->fractalManager->createData($itemsResource)->toArray();
        return $this->apiManager->respond($processedItems);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $this->initialiseRequest();
        $response = $this->queryRepository->find($id);
        if ($response instanceof PKResponseResourceNotFound) {
            return $this->apiManager->respondNotFound('Resource not found.');
        }
        $itemResource = new Item($response->resource, $this->transformer);
        $processedItems = $this->fractalManager->createData($itemResource);
        return $this->apiManager->respond($processedItems->toArray());
    }
    /**
     * Store a newly created resource in storage.
     *
     */
    public function store()
    {
        $response = $this->queryRepository->create($this->initialiseRequest()->all());
        if ($response instanceof PKResponseResourceCreateError) {
            $this->apiManager->setStatusCode($response->statusCode)->respondWithError($response->message);
        }
        else if($response instanceof PKResponseResourceCreateSuccessful){
            $itemResource = new Item($response->resource, $this->transformer);
            $processedItems = $this->fractalManager->createData($itemResource);
            return $this->apiManager->setStatusCode($response->statusCode)->respond($processedItems->toArray());
        }
        else{
            $this->apiManager->setStatusCode($response->statusCode)->respondWithMessage($response->message);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $requestParameters = $this->requestParametersOrFail();
        $response = $this->queryRepository->update($requestParameters, $id);
        if( $response instanceof PKResponseResourceNotFound ||
            $response instanceof PKResponseResourceUpdateError ||
            $response instanceof PKResponseResourceUpdateMassAssignmentError
        ){
            return $this->apiManager->setStatusCode($response->statusCode)->respondWithError($response->message);
        }
        else if($response instanceof PKResponseResourceUpdateSuccessful){
            $itemResource = new Item($response->resource, $this->transformer);
            $processedItems = $this->fractalManager->createData($itemResource);
            return $this->apiManager->setStatusCode($response->statusCode)->respond($processedItems->toArray());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->initialiseRequest();
        $response = $this->queryRepository->delete($id);
        if( $response instanceof PKResponseResourceNotFound ||
            $response instanceof PKResponseResourceDeleteError){
            return $this->apiManager->setStatusCode($response->statusCode)->respondWithError($response->message);
        }
        else if($response instanceof PKResponseResourceDeleteSuccessful){
            return $this->apiManager->setStatusCode($response->statusCode)->respondWithSuccess($response->message);
        }
    }
    protected function requestParametersOrFail()
    {
        $requestParameters = $this->initialiseRequest()->all();
        if (!$requestParameters){
            return $this->apiManager->setStatusCode(400)->respondWithError("No parameters specified");
        }
        return $requestParameters;
    }
}