<?php
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'Abstract.php' );


/**
 * 
 * @version 1.0
 * @author Dinahosting
 */
class HttpRequest_Response_Html extends HttpRequest_Response_Abstract
{
	
	public function __construct()
	{
	}
	
	/**
	 * @method factory
	 * @return HttpRequest_Response_Html
	 */
	public static function factory()
	{
		return new self();
	}
	
	/**
	 * @method parseHeaders
	 * @param string $response
	 * @param string $headers 
	 * @return string
	 */
	public function parseResponse($response, $headers = null)
	{
		$html = $this->parseHeaders($response, $headers);
		
		$this->body = $html;
		
		return $this;
	}

}
