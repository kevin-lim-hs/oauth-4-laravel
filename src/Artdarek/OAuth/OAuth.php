<?php
/**
 * @author     Dariusz Prząda <artdarek@gmail.com>
 * @copyright  Copyright (c) 2013
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace Artdarek\OAuth;

use Illuminate\Support\ServiceProvider;

use \Config;
use \URL;

use \OAuth\ServiceFactory;
use \OAuth\Common\Consumer\Credentials;

class OAuth
{
    /**
     * @var ServiceFactory
     */
    private $_serviceFactory;

    /**
     * Client ID from config
     * @var string
     */
    private $_client_id;

    /**
     * Client secret from config
     * @var string
     */
    private $_client_secret;

    /**
     * Scope from config
     * @var array
     */
    private $_scope = array();

    /**
     * Constructor
     *
     * @param ServiceFactory $serviceFactory - (Dependency injection) If not provided, a ServiceFactory instance will be constructed.
     */
    public function __construct(ServiceFactory $serviceFactory)
    {
        $this->_serviceFactory = $serviceFactory;
    }

    /**
     * Detect config and set data from it
     *
     * @param string $service
     */
    public function setConfig( $service )
    {
        // if config/oauth-4-laravel.php exists use this one
        if ( Config::get('oauth-4-laravel.consumers') != null ) {

            $this->_client_id = Config::get("oauth-4-laravel.consumers.$service.client_id");
            $this->_client_secret = Config::get("oauth-4-laravel.consumers.$service.client_secret");
            $this->_scope = Config::get("oauth-4-laravel.consumers.$service.scope", array() );

        // else try to find config in packages configs
        } else {
            $this->_client_id = Config::get("oauth-4-laravel::consumers.$service.client_id");
            $this->_client_secret = Config::get("oauth-4-laravel::consumers.$service.client_secret");
            $this->_scope = Config::get("oauth-4-laravel::consumers.$service.scope", array() );
        }
    }

    /**
     * Set the http client object
     *
     * @param string $httpClientName
     * @return void
     */
    public function setHttpClient($httpClientName)
    {
        $httpClientClass = "\\OAuth\\Common\\Http\\Client\\$httpClientName";
        $this->_serviceFactory->setHttpClient(new $httpClientClass());
    }

    /**
     * @param  string $service
     * @param  string $url
     * @param  array  $scope
     * @return \OAuth\Common\Service\AbstractService
     */
    public function consumer( $service, $url = null, $scope = null )
    {
        // get config
        $this->setConfig( $service );

        // get storage object
        $storage =

        // create credentials object
        $credentials = new Credentials(
            $this->_client_id,
            $this->_client_secret,
            $url ?: URL::current()
        );

        // check if scopes were provided
        if (is_null($scope))
        {
            // get scope from config (default to empty array)
            $scope = $this->_scope;
        }

        // return the service consumer object
        return $this->_serviceFactory->createService($service, $credentials, $storage, $scope);

    }
}
