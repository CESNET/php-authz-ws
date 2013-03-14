<?php

namespace InAuthzWs\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use InAuthzWs\Handler\ResourceHandlerInterface;
use Zend\Mvc\MvcEvent;
use PhlyRestfully\ApiProblem;
use PhlyRestfully\HalResource;
use PhlyRestfully\HalCollection;
use InAuthzWs\Handler\Exception\ResourceDataValidationException;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;
use Zend\Log\Logger;
use Zend\Http\Request;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Json\Json;
use InAuthzWs\Client\Authenticator\AuthenticatorInterface;
use InAuthzWs\Client\Authenticator\Result;


class ResourceController extends AbstractRestfulController implements LoggerAwareInterface
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

    /**
     * Collection page size, if required.
     * 
     * @var integer
     */
    protected $collectionPageSize = 10;

    /**
     * Collection name.
     * 
     * @var string
     */
    protected $collectionName = 'items';

    /**
     * Resource handler.
     * 
     * @var ResourceHandlerInterface
     */
    protected $resourceHandler = null;

    /**
     * Logger.
     * 
     * @var Logger
     */
    protected $logger = null;

    /**
     * Client authenticator.
     * 
     * @var AuthenticatorInterface
     */
    protected $clientAuthenticator = null;

    /**
     * Request signature.
     * 
     * @var string
     */
    protected $requestSignature = '';


    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * Sets the resource handler.
     * 
     * @param ResourceHandlerInterface $handler
     */
    public function setResourceHandler(ResourceHandlerInterface $handler)
    {
        $this->resourceHandler = $handler;
    }


    /**
     * Returns the resource handler.
     * 
     * @return ResourceHandlerInterface
     */
    public function getResourceHandler()
    {
        return $this->resourceHandler;
    }


    /**
     * Sets the client authentricator.
     * 
     * @param AuthenticatorInterface $clientAuthenticator
     */
    public function setClientAuthenticator(AuthenticatorInterface $clientAuthenticator)
    {
        $this->clientAuthenticator = $clientAuthenticator;
    }


    /**
     * Returns the client authenticator.
     * 
     * @return AuthenticatorInterface|null
     */
    public function getClientAuthenticator()
    {
        return $this->clientAuthenticator;
    }


    /**
     * Sets the request signature.
     * 
     * @param string $requestSingature
     */
    public function setRequestSignature($requestSingature)
    {
        $this->requestSignature = $requestSingature;
    }


    /**
     * Returns the request signature.
     * 
     * @return string
     */
    public function getRequestSignature()
    {
        if (! $this->requestSignature) {
            $request = $this->getRequest();
            /* @var $request \Zend\Http\Request */
            
            $this->requestSignature = sprintf("%s %s", $request->getMethod(), $this->route);
        }
        
        return $this->requestSignature;
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


    /**
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\AbstractRestfulController::onDispatch()
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->setRequestSignature($this->createRequestSignature($e->getRequest(), $e->getRouteMatch()));
        
        if (! $this->resourceHandler) {
            throw new Exception\MissingResourceHandlerException();
        }
        
        if (! $this->route) {
            throw new Exception\MissingRouteException();
        }
        
        $authenticationResult = $this->authenticateClient();
        if (null === $authenticationResult || $authenticationResult->isValid()) {
            $return = parent::onDispatch($e);
        } else {
            $this->log(sprintf("Unauthorized: [%s] %s", $authenticationResult->getCode(), implode('; ', $authenticationResult->getMessages())));
            $return = $this->errorResponse(401, 'Authorization required: ' . $authenticationResult->getCode());
        }
        
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
        try {
            $resource = $this->resourceHandler->fetch($id, $this->getEnvParams());
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Error fetching resource', $e);
        }
        
        if (! $resource) {
            return $this->errorResponse(404, 'Resource not found');
        }
        
        $this->log(sprintf("FOUND: %s", Json::encode($resource)));
        
        return new HalResource($resource, $id, $this->route);
    }


    public function getList()
    {
        $response = $this->getResponse();
        
        try {
            $data = $this->resourceHandler->fetchAll($this->getEnvParams());
        } catch (ResourceDataValidationException $e) {
            $this->log(sprintf("Validation exception: %s", Json::encode($e->getValidationMessages())), Logger::DEBUG);
            return $this->errorResponse(400, $e->getMessage(), $e);
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Error retrieving collection', $e);
        }
        
        $items = $data['items'];
        $collection = new HalCollection($items, $this->route, $this->route);
        $collection->setPage($this->getRequest()
            ->getQuery('page', 1));
        $collection->setPageSize($this->collectionPageSize);
        $collection->setCollectionName($this->collectionName);
        $collection->setAttributes(array(
            'count' => $data['count'], 
            'params' => $data['params']
        ));
        
        $this->log(sprintf("Found %d item(s): %s", $data['count'], Json::encode($data['params'])));
        
        return $collection;
    }


    public function create($data)
    {
        try {
            $resource = $this->resourceHandler->create($data, $this->getEnvParams());
        } catch (ResourceDataValidationException $e) {
            $this->log(sprintf("Validation exception: %s", Json::encode($e->getValidationMessages())), Logger::DEBUG);
            return $this->errorResponse(400, $e->getMessage(), $e);
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Error creating resource', $e);
        }
        
        if (! isset($resource['id'])) {
            return $this->errorResponse(422, 'No resource identifier present following resource creation.');
        }
        
        $id = $resource['id'];
        
        $response = $this->getResponse();
        $response->setStatusCode(201);
        
        $this->log(sprintf("CREATED: %s", Json::encode($resource)));
        
        return new HalResource($resource, $id, $this->route);
    }


    public function update($id, $data)
    {
        return $this->errorResponse(501, 'Not implemented');
    }


    public function delete($id)
    {
        try {
            if (! $this->resourceHandler->delete($id, $this->getEnvParams())) {
                return $this->errorResponse(422, 'Unable to delete resource.');
            }
        } catch (\Exception $e) {
            return $this->errorResponse(500, 'Error deleting resource', $e);
        }
        
        $response = $this->getResponse();
        $response->setStatusCode(204);
        
        $this->log('DELETED');
        
        return $response;
    }


    public function getEnvParams()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        return array(
            'query' => $request->getQuery(), 
            'headers' => $request->getHeaders()
        );
    }


    /**
     * Tries to authenticate the client if an authenticator is set. Otherwise returns null.
     * 
     * @return Result|null
     */
    protected function authenticateClient()
    {
        $result = null;
        
        if ($clientAuthenticator = $this->getClientAuthenticator()) {
            $result = $clientAuthenticator->authenticate($this->getRequest());
        }
        
        return $result;
    }


    protected function errorResponse($code, $message,\Exception $e = null)
    {
        $this->log(sprintf("Error [%s] %s", $code, $message), Logger::ERR);
        
        if ($e) {
            $this->log(sprintf("Exception [%s]: %s", get_class($e), $e->getMessage()), Logger::DEBUG);
        }
        
        $message = sprintf("%s [%s]", $message, $this->getRequestSignature());
        
        return new ApiProblem($code, $message);
    }


    protected function createRequestSignature(Request $request, RouteMatch $routeMatch)
    {
        $route = $routeMatch->getMatchedRouteName();
        $id = $routeMatch->getParam('id');
        if ($id) {
            $route .= '/' . $id;
        }
        
        return sprintf("%s %s (%s)", $request->getMethod(), $route, uniqid());
    }


    protected function log($message, $priority = Logger::INFO)
    {
        if ($this->logger instanceof Logger) {
            $message = sprintf("[%s] %s", $this->getRequestSignature(), $message);
            $this->logger->log($priority, $message);
        }
    }
}