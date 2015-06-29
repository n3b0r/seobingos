<?php

/**
 * 
 * @version 1.0
 * @author Dinahosting
 */
abstract class HttpRequest_Response_Abstract
{
	protected $body = '';
	protected $headers = array ();

	
	/**
	 * @method __toString
	 * @return string
	 */
	public function __toString()
	{
		$body = $this->getBody();
		if(is_object($body) && method_exists($body, '__toString')) 
		{
			$body = $body->__toString();	
		}
		else if(is_array($body))
		{
			$body = var_export($body,true);	
		} 
		
		return (string)$body;
	}
	
	/**
	 * @method getStatusCode
	 * @return string
	 */
	public function getStatusCode()
	{
		return ( isset( $this->headers['Status-Code']) ? $this->headers['Status-Code'] : '' );
	}
	
	
	/**
	 * @method parseResponse
	 * @param string $response
	 * @param string $headers
	 * @return string
	 */
	abstract function parseResponse( $response, $headers = null );
	
	
	/**
	 * @method parseHeaders
	 * @param string $response
	 * @param string $headers
	 * @return string
	 */
	protected function parseHeaders( $response, $headers = null )
	{
		// Extract headers from response
		if( is_null($headers) )
		{
			$pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
			preg_match_all ( $pattern, $response, $matches );
			$headers = $matches [0];
			$headersArray = explode ( "\r\n", str_replace ( "\r\n\r\n", '', array_pop ( $headers ) ) );
		}
		else
		{
			$headersArray = explode( "\r\n", $headers );
		}
		
		// Extract the version and status from the first header
		$version_and_status = array_shift ( $headersArray );
		preg_match ( '#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches );
		$this->headers ['Http-Version'] = $matches [1];
		$this->headers ['Status-Code'] = $matches [2];
		$this->headers ['Status'] = $matches [2] . ' ' . $matches [3];
		
		// Convert headers into an associative array
		foreach ( $headersArray as $header )
		{
			preg_match ( '#(.*?)\:\s(.*)#', $header, $matches );
			if( count( $matches ) > 2 )
			{
				$this->headers [$matches [1]] = $matches [2];
			}
		}
		
		// Remove the headers from the response body
		$body = ( isset($pattern) ) ? preg_replace ( $pattern, '', $response ) : $response;
		
		return $body;
	}
	
	/**
	 * @method getBody
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}
	
	/**
	 * @method setBody
	 * @param string $body
	 */
	public function setBody($body)
	{
		$this->body = $body;
		return $this;
	}
	
	/**
	 * @method setHeaders
	 * @param array $headers
	 */
	public function setHeaders( array $headers )
	{
		$this->headers = $headers;
		return $this;
	}

}
