<?php 
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'Exception.php' );

/**
 * 
 * @version 1.0
 * @author Dinahosting
 */
abstract class HttpRequest_Abstract
{
	protected $cookie_file;
	protected $headers = array ();
	protected $options = array ();
	protected $referer = '';
	protected $user_agent = '';
	
	protected $method;
	
	protected $url = null;
	protected $error = '';
	protected $handle = null;

	const HTTP_AUTH_BASIC = 'authBasic';
	const HTTP_AUTH_DIGEST = 'authDigest';
	
	/**
	 * @method __construct   
	 * @param string $url
	 */
	public function __construct($url = '')
	{
		$this->user_agent = isset ( $_SERVER ['HTTP_USER_AGENT'] ) ? $_SERVER ['HTTP_USER_AGENT'] : 'Curl/PHP ' . PHP_VERSION . '';
		
		$this->setUrl($url);
		
		$this->error = null;
		
		$this->handle = ( empty($url) ? curl_init() : curl_init($url) );
		curl_setopt ( $this->handle, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $this->handle, CURLOPT_HEADER, true );
		curl_setopt ( $this->handle, CURLOPT_RETURNTRANSFER, true );
	}
	
	/**
	 * @method factory
	 * @return HttpRequest 
	 */
	public static function factory($url = '')
	{
		return new self($url);
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
			
		if( false == is_null($this->handle) )
		{
			curl_setopt ( $this->handle, CURLOPT_URL, $url );
		}
		
		return $this;
	}
	
	/**
	 * @param string $host
	 * @param int $port
	 * @return HttpRequest
	 */
	public function setProxy( $host, $port = 8080 )
	{
		if( false == is_null($this->handle) )
		{
			curl_setopt($this->handle, CURLOPT_PROXY, $host.':'.$port);
		}
		
		return $this;
	}
	
	/**
	 * @method setReferer
	 * @param string $referer
	 * @return HttpRequest
	 */
	public function setReferer($referer)
	{
		if( false == is_string($referer) )
		{
			throw new HttpRequest_Exception( 'setReferer() requires string parameter' );
		}
		$this->referer = $referer;
		
		return $this;
	}

	/**
	 * @method setUA
	 * @param string $userAgent
	 * @return HttpRequest
	 */
	public function setUA($userAgent)
	{
		if( false == is_string($userAgent) )
		{
			throw new HttpRequest_Exception( 'setUA() requires string parameter' );
		}
		$this->user_agent = $userAgent;
		
		return $this;
	}
	
	/**
	 * @method setTimeOut
	 * @param int $responseTimeout 
	 * @param int $connectionTimeout 
	 * @return HttpRequest
	 */
	public function setTimeOut($responseTimeout = 60, $connectionTimeout = 6)
	{
		if( false == is_numeric($responseTimeout) || false == is_numeric($connectionTimeout) )
		{
			throw new HttpRequest_Exception( 'setTimeOut() requires numeric parameters' );
		}
		if( false == is_null($this->handle) )
		{
			curl_setopt($this->handle, CURLOPT_TIMEOUT, (int)$responseTimeout);
			curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, (int)$connectionTimeout);
		}
		return $this;
	}

	/**
	 * @method setAuthentication
	 * @param string $username
	 * @param string $password
	 * @param string $authType
	 * @return HttpRequest 
	 */
	public function setAuthentication($username, $password, $authType = self::HTTP_AUTH_BASIC)
	{
		if( is_null($this->handle) ) 
		{
			return;	
		}
		if( false == is_string($username) || false == is_string($password) || false == is_string($authType) )
		{
			throw new HttpRequest_Exception( 'setAuthentication() requires string parameters' );
		}
		if( $authType == self::HTTP_AUTH_BASIC )
		{
			curl_setopt($this->handle, CURLOPT_USERPWD, $username.':'.$password);
			curl_setopt($this->handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}
		else if( $authType == self::HTTP_AUTH_DIGEST )
		{
			curl_setopt($this->handle, CURLOPT_USERPWD, $username.':'.$password);
			curl_setopt($this->handle, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		}
		
		return $this;
	}

	/**
	 * @method getError
	 * @return mixed
	 */
	public function getError()
	{
		return $this->error;
	}
	
	/**
	 * @method getError
	 * @return mixed
	 */
	public function getErrorMsg()
	{
		return is_object($this->error) ? $this->error->getMessage() : null;
	}
	
	/**
	 * @method error
	 * @return mixed
	 */
	public function hasError()
	{
		return ( false == is_null($this->error) ); // not null means there is a error msg.
	}

	/**
	 * @method setOptions
	 * @param string $url URL for sending request.
	 * @param array|string $vars Request parameters.
	 * @return HttpRequest
	 */
	protected function setOptions($vars)
	{
		if( is_null($this->handle) )
		{
			return;
		} 
		
		// Set some default CURL options
		curl_setopt ( $this->handle, CURLOPT_COOKIEFILE, $this->cookie_file );
		curl_setopt ( $this->handle, CURLOPT_COOKIEJAR, $this->cookie_file );
		if( !empty($vars) && ($this->method != 'GET') )
		{
			curl_setopt ( $this->handle, CURLOPT_POSTFIELDS, (is_array ( $vars ) ? http_build_query ( $vars, '', '&' ) : $vars) );
		}
		curl_setopt ( $this->handle, CURLOPT_REFERER, $this->referer );
		
		curl_setopt ( $this->handle, CURLOPT_USERAGENT, $this->user_agent );
		
		// Format custom headers for this request and set CURL option
		$headers = array ();
		foreach ( $this->headers as $key => $value )
		{
			$headers [] = $key . ': ' . $value;
		}
		curl_setopt ( $this->handle, CURLOPT_HTTPHEADER, $headers );
		
		// Set any custom CURL options
		foreach ( $this->options as $option => $value )
		{
			curl_setopt ( $this->handle, constant ( 'CURLOPT_' . str_replace ( 'CURLOPT_', '', strtoupper ( $option ) ) ), $value );
		}
		
		return $this;
	}
	
	/**
	 * @method verifySslPeer
	 * @param bool $set  Set verify peer. 
	 * @return HttpRequest
	 */
	public function verifySslPeer( $set = false )
	{
		if( is_null($this->handle) )
		{
			return;
		} 
		curl_setopt ( $this->handle, CURLOPT_SSL_VERIFYPEER, $set );
		
		return $this;
	}

	/**
	 * @method request
	 * @param string $method   ( GET, PUT, POST, DELETE ... ).
	 * @param array|string $vars Request parameters.
	 * @return HttpRequest_Response_Html
	 */
	protected function request($method, $vars = array())
	{
		if( is_null($this->handle) ) 
		{
			throw new HttpRequest_Exception( 'null handle' );
		}
		
		if( false == is_string($this->url) || empty($this->url) ) 
		{
			throw new HttpRequest_Exception( 'request() requires url attribute is set' );
		}
		
		if( false == is_string($method) || empty($method) )
		{
			throw new HttpRequest_Exception('request() $method must be a not-empty string');
		}
		
		if( false == is_string($vars) && false == is_array($vars) )
		{
			throw new HttpRequest_Exception('request() $vars must be a string or array');
		}
		 
		// Determine the request method and set the correct CURL option
		switch ($method)
		{
			case 'GET' :
				curl_setopt ( $this->handle, CURLOPT_HTTPGET, true );
				$this->method = $method;
				break;
			case 'POST' :
				curl_setopt ( $this->handle, CURLOPT_POST, true );
				$this->method = $method;
				break;
			case 'HEAD' :
				curl_setopt ( $this->handle, CURLOPT_HEADER, true ); 
				curl_setopt ( $this->handle, CURLOPT_NOBODY, true );
				$this->method = $method;
				break;
			case 'DELETE' :
				curl_setopt ( $this->handle, CURLOPT_HEADER, false ); 
				curl_setopt ( $this->handle, CURLOPT_CUSTOMREQUEST, $method );
				$this->method = $method;
				break;
			case 'PUT' :
				$query = count($vars) ? http_build_query($vars) : '';
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($query))); 
				curl_setopt ( $this->handle, CURLOPT_CUSTOMREQUEST, $method );
				$this->method = $method;
				break;
			case 'CUSTOMPOST' :
				curl_setopt ( $this->handle, CURLOPT_CUSTOMREQUEST, 'POST' );
				$this->method = 'POST';
				break;
			default :
				curl_setopt ( $this->handle, CURLOPT_CUSTOMREQUEST, $method );
				$this->method = $method;
				break;
		}
			
		$this->setOptions($vars);
		
		$response = curl_exec ( $this->handle );
		
		return $this->parseResponse($response);
	}
	
	/**
	 * @method parseResponse  Parses response from request.
	 * @param string parseResponse
	 * @return HttpRequest_Response_Html
	 */
	protected function parseResponse($response)
	{
		if ($response)
		{
			$curl_info = curl_getinfo( $this->handle );
			if( isset($curl_info['header_size']) )
			{
				$header_size = $curl_info['header_size'];
				$header = substr($response, 0, $header_size);
				$response = substr($response, $header_size );
			}
			else
			{
				$header = null;
			}
			$response = HttpRequest_Response_Html::factory()->parseResponse( $response, $header );
		}
		else
		{
			$this->error = curl_errno ( $this->handle ) . ' - ' . curl_error ( $this->handle );
		}
		return $response;
	}
	
	/**
	 * @method __destruct   Destructor
	 */
	public function __destruct()
	{
		@curl_close ( $this->handle );
	}
}