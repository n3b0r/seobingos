<?php 
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'Abstract.php' );
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'XmlRpcInterface.php' );
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'Response'.DIRECTORY_SEPARATOR.'Xml.php' );

/**
 * XmlRpc request by Curl.
 * 
 * Requires libcurl.  http://php.net/manual/en/book.curl.php
 * 
 * @version 1.0
 * @author Dinahosting
 */
class HttpRequest_XmlRpc extends HttpRequest_Abstract implements HttpRequest_XmlRpcInterface
{
	const CLASSNAME_EXCEPTION = 'Exception'; // modify for Custom exceptions if required
	
	/**
	 * @method __construct
	 * @param string $url
	 */
	public function __construct($url = '')
	{
		if( false == function_exists('xmlrpc_encode_request') )
		{
			throw new HttpRequest_Exception('Cant run commands without xmlrpc_encode_request() function');
		}
		parent::__construct( $url );
	}
	
	/**
	 * @method factory
	 * @param string $url
	 * @return HttpRequest_XmlRpc
	 */
	public static function factory($url = '')
	{
		return new self($url);
	}
	

	
	/**
	 * @method setOptions
	 * @param array|string $vars Request parameters.
	 * @return HttpRequest_XmlRpc
	 */
	protected function setOptions($vars)
	{
		if( is_null($this->handle) ) 
		{
			return;	
		}
		
		$this->headers = array();
		array_push($this->headers,'Content-Type: text/xml');
		array_push($this->headers,'Content-Length: '.strlen($vars));
		array_push($this->headers,'\r\n');

		curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->headers );

		if( count($vars) && ($this->method != 'GET') )
		{
			curl_setopt($this->handle, CURLOPT_POSTFIELDS, $vars);
		}
		
		return $this;
	}

	/**
	 * @method request
	 * @param string $method   ( GET, PUT, POST, DELETE ... ).
	 * @param array|string $vars Request parameters.
	 * @return HttpRequest_Response_Xml
	 */
	protected function request($method, $vars = array())
	{
		return parent::request($method, $vars);
	}
	
	/**
	 * @method sendRequest
	 * @param string $remoteMethod
	 * @param array $params
	 * @return HttpRequest_Response_Xml 
	 */
	public function sendRequest($remoteMethod, array $params)
	{
		$this->error = NULL;
		
		$request = xmlrpc_encode_request($remoteMethod, $params);
		$this->body = $request;
		
		return $this->request('CUSTOMPOST', $request);
	}
	
	/**
	 * @method parseResponse  Parses response from request.
	 * @param string parseResponse
	 * @return HttpRequest_Response_Xml
	 */
	protected function parseResponse($response)
	{
		$exceptionClassName = self::CLASSNAME_EXCEPTION;
		if( !class_exists($exceptionClassName) )
		{
			$exceptionClassName = 'Exception';
		}
			
		$parsed_response = NULL;
		$responseXmlObj = HttpRequest_Response_Xml::factory();
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
			
			$parsed_response = $responseXmlObj->parseResponse( $response, $header )->getBody();

			$body = $responseXmlObj->getBody();

			if( $responseXmlObj->getStatusCode() !== '200' )
			{
				$this->error = new $exceptionClassName( (string)$responseXmlObj, $responseXmlObj->getStatusCode() );
			}
			else if( is_array($body) && isset( $body['faultCode'] ) && isset( $body['faultString'] ) )
			{
				$this->error = new $exceptionClassName( $body['faultString'], $body['faultCode'] );
			}
			else if( is_array($body) && isset( $body['responseCode'] ) && isset( $body['errors'] ) )
			{
				$err = current($body['errors']);
				$this->error = new $exceptionClassName( $err['message'], $err['code'] );
			}
		}
		else
		{
			$this->error = new $exceptionClassName( curl_error ( $this->handle ), curl_errno ( $this->handle ) );
		}
		return $responseXmlObj;
	}


	/**
	 * @method __destruct   Destructor
	 */
	public function __destruct()
	{
		parent::__destruct();
	}
	
}