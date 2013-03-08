<?php

namespace InAuthzWs\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use InAuthzWs\Handler\ResourceHandlerInterface;
use Zend\Mvc\MvcEvent;
use PhlyRestfully\ApiProblem;
use PhlyRestfully\HalResource;
use PhlyRestfully\HalCollection;


class ResourceController extends AbstractRestfulController
{

    /**
     * Criteria for the AcceptableViewModelSelector
     *
     * @var array
     */
    protected $acceptCriteria = array(
        'PhlyRestfully\View\RestfulJsonModel' => array(
            '*/json'
        )
    );

    /**
     * Route name that resolves to this resource; used to generate links.
     *
     * @var string
     */
    protected $route;

    protected $collectionPageSize = 10;

    protected $collectionName = 'items';

    /**
     * Resource handler.
     * 
     * @var ResourceHandlerInterface
     */
    protected $resourceHandler;


    public function setResourceHandler(ResourceHandlerInterface $handler)
    {
        $this->resourceHandler = $handler;
    }


    public function getResourceHandler()
    {
        return $this->resourceHandler;
    }


    /**
     * Inject the route name for this resource.
     *
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }


    public function onDispatch(MvcEvent $e)
    {
        _dump($e->getRouteMatch());
        if (! $this->resourceHandler) {
            throw new Exception\MissingResourceHandlerException();
        }
        
        if (! $this->route) {
            throw new Exception\MissingRouteException();
        }
        
        $return = parent::onDispatch($e);
        
        $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);
        $viewModel->setVariables(array(
            'payload' => $return
        ));
        
        if ($viewModel instanceof RestfulJsonModel) {
            $viewModel->setTerminal(true);
        }
        
        $e->setResult($viewModel);
        return $viewModel;
    }


    public function get($id)
    {
        $resource = $this->resourceHandler->fetch($id);
        if (! $resource) {
            return new ApiProblem(404, 'Resource not found');
        }
        
        return new HalResource($resource, $id, $this->route);
    }


    public function getList()
    {
        $response = $this->getResponse();
        $items = $this->resourceHandler->fetchAll($this->getEnvParams());
        
        $collection = new HalCollection($items, $this->route, $this->route);
        $collection->setPage($this->getRequest()
            ->getQuery('page', 1));
        $collection->setPageSize($this->collectionPageSize);
        $collection->setCollectionName($this->collectionName);
        return $collection;
    }


    public function create($data)
    {}


    public function update($id, $data)
    {}


    public function delete($id)
    {}


    public function getEnvParams()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        return array(
            'query' => $request->getQuery(), 
            'headers' => $request->getHeaders()
        );
    }
}