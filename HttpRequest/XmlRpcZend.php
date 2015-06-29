<?php 
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'Abstract.php' );
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'XmlRpcInterface.php' );
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'Response'.DIRECTORY_SEPARATOR.'Html.php' );

/**
 * XmlRpc request over Zend XmlRpc Client.
 * 
 * Requires Zend Framework.
 * 
 * @version 1.0
 * @author Dinahosting
 */
class HttpRequest_XmlRpcZend extends HttpRequest_Abstract implements HttpRequest_XmlRpcInterface
{

	/**
     * Zend Xml Client classname.
     */
	const ZEND_XMLRPC_CLIENT_CLASSNAME = 'Zend_XmlRpc_Client';
	
	/**
     * Rethrow Zend Xml Client exception ( true ) or capture it ( false ).
     */
	const RETHROW_CLIENT_ERROR = false;
	
	/**
     * Sets skip system lookup call .
     * @var bool
     */
	protected $skipSystemLookup = true; // if true, faster and less resource. if false, cast an additional request and requires system.methodSignature  on server.

	/**
     * Zend Xml Rpc client object.
     * @var mixed
     */
	protected $clientZendXmlRpc = null;
	
	/**
	 * @method __construct   
	 * @param string $url
	 */
	public function __construct($url = '')
	{
		$this->setUrl($url);
		
		$className = self::ZEND_XMLRPC_CLIENT_CLASSNAME;
		if( class_exists($className) )
		{
			$this->clientZendXmlRpc = new $className( $this->url );
			$this->setSkipSystemLookup( $this->skipSystemLookup );
		}
		else
		{
			throw new HttpRequest_Exception('Cannot load '.self::ZEND_XMLRPC_CLIENT_CLASSNAME.' class');
		}
	}
	
	/**
	 * @method factory
	 * @param string $url
	 * @return HttpRequest_XmlRpcZend 
	 */
	public static function factory($url = '')
	{
		return new self($url);
	}
	
	
	/**
	 * @method setAuthentication
	 * @param string $username
	 * @param string $password
	 * @param string $authType
	 * @return HttpRequest_XmlRpcZend 
	 */
	public function setAuthentication($username, $password, $authType = self::HTTP_AUTH_BASIC)
	{
		if( false == is_string($username) || false == is_string($password) || false == is_string($authType) )
		{
			throw new HttpRequest_Exception( 'setAuthentication() requires string parameters' );
		}
		if( $authType == self::HTTP_AUTH_BASIC )
		{
			$this->clientZendXmlRpc->getHttpClient()->setAuth($username, $password, Zend_Http_Client::AUTH_BASIC);
		}
		else if( $authType == self::HTTP_AUTH_DIGEST )
		{
			throw new HttpRequest_Exception('Not implemented');
		}
		else
		{
			// NO AUTH
		}
		return $this;
	}
	
	/**
	 * @method setUrl
	 * @param string $url
	 * @return HttpRequest
	 */
	public function setUrl($url)
	{
		if( false == is_string($url) || empty($url) )
		{
			throw new HttpRequest_Exception( 'setUrl() requires string not-empty parameter' );
		}

		$this->url = $url;
			
		if( false == is_null($this->clientZendXmlRpc) )
		{
			$this->clientZendXmlRpc->getHttpClient()->setUri($url);
		}
		
		return $this;
	}

	/**
	 * @method setSkipSystemLookup
	 * @param bool $skipSystemLookup
	 * @return HttpRequest_XmlRpcZend 
	 */
	public function setSkipSystemLookup($skipSystemLookup)
	{
		if( false == is_bool($skipSystemLookup) )
		{
			throw new HttpRequest_Exception( 'setSkipSystemLookup() requires bool parameter' );
		}
		$this->skipSystemLookup = $skipSystemLookup;
		
		if( false == is_null($this->clientZendXmlRpc) )
		{
			$this->clientZendXmlRpc->setSkipSystemLookup( $this->skipSystemLookup );
		}
		
		return $this;
	}


	/**
	 * @method setTimeOut
	 * @param int $responseTimeout 
	 * @param int $connectionTimeout   UNUSED
	 * @return HttpRequest
	 */
	public function setTimeOut($responseTimeout = 60, $connectionTimeout = 6)
	{
		if( false == is_numeric($responseTimeout) )
		{
			throw new HttpRequest_Exception( 'setTimeOut() requires numeric parameter' );
		}
		if( false == is_null($this->clientZendXmlRpc) )
		{
			$this->clientZendXmlRpc->getHttpClient()->setConfig(array('timeout' => (int) $responseTimeout));
		}
		
		return $this;
	}
	
	/**
	 * @method sendRequest
	 * @param string $remoteMethod   Called remote method.
	 * @param array $params   Params for remote method call.
	 * @return HttpRequest_Response_Html 
	 */
	public function sendRequest($remoteMethod, array $params)
	{
		$this->error = NULL;
		
		return $this->request($remoteMethod, $params);
	}
	
	
	/**
	 * @method request
	 * @param string $remoteMethod   Called remote method.
	 * @param array $params  Params for remote method call.
	 * @return HttpRequest_Response_Html
	 */
	protected function request($remoteMethod, $params = array())
	{
		if( is_null($this->clientZendXmlRpc) || false === is_object($this->clientZendXmlRpc) )
		{
			throw new HttpRequest_Exception('XmlRpc Client not loaded');
		}
		if( false == is_string($remoteMethod) || empty($remoteMethod) )
		{
			throw new HttpRequest_Exception('request() $remoteMethod must be a not-empty string');
		}
		if( false == is_array($params) )
		{
			throw new HttpRequest_Exception('request() $params must be an array');
		}
		
		$data = NULL;
		try
		{
			$data = $this->clientZendXmlRpc->call($remoteMethod, $params);
			$this->error = $this->clientZendXmlRpc->getLastResponse()->getFault();
		}
		catch (Exception $e)
		{
			$this->error = $e;

			if( true === self::RETHROW_CLIENT_ERROR )
			{
				throw $e;
			}
		}
		return $this->parseResponse($data);
	}
	
	/**
	 * @method setOptions
	 * @param mixed $vars Request parameters.
	 * @return HttpRequest_XmlRpcZend
	 */
	protected function setOptions($vars)
	{
		return $this;
	}
	
	/**
	 * @method parseResponse  Parses response from request.
	 * @param string parseResponse
	 * @return HttpRequest_Response_Html
	 */
	protected function parseResponse($response)
	{
		return HttpRequest_Response_Html::factory()->setBody($response);
	}
	
	/**
	 * @method setReferer
	 * @param string $referer
	 */
	public function setReferer($referer)
	{
		throw new HttpRequest_Exception('Not implemented');
	}

	/**
	 * @method setUA
	 * @param string $userAgent
	 */
	public function setUA($userAgent)
	{
		throw new HttpRequest_Exception('Not implemented');
	}

	/**
	 * @method __destruct   Destructor
	 */
	public function __destruct()
	{
	}
	
}