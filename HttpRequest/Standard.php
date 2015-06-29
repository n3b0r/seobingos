<?php 
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'Abstract.php' );
include_once( str_replace('//','/',dirname(__FILE__)).DIRECTORY_SEPARATOR.'Response'.DIRECTORY_SEPARATOR.'Html.php' );

/**
 * Standard HTTP Request by Curl.
 * 
 * Requires libcurl.  http://php.net/manual/en/book.curl.php
 * 
 * @version 1.0
 * @author Dinahosting
 */
class HttpRequest_Standard extends HttpRequest_Abstract
{

	/**
	 * @method __construct
	 * @param string $url
	 */
	public function __construct($url = '')
	{
		parent::__construct($url);
	}
	
	/**
	 * @method factory
	 * @param string $url
	 * @return HttpRequest_Standard 
	 */
	public static function factory($url = '')
	{
		return new self($url);
	}


	/**
	 * @method get
	 * @return HttpRequest_Response_Html
	 */
	public function get()
	{
		return $this->request ( 'GET' );
	}

	/**
	 * @method post
	 * @param array $vars Request parameters.
	 * @return HttpRequest_Response_Html
	 */
	public function post($vars = array())
	{
		return $this->request ( 'POST', $vars );
	}

	/**
	 * @method put
	 * @param array $vars Request parameters.
	 * @return HttpRequest_Response_Html
	 */
	public function put($vars = array())
	{
		return $this->request ( 'PUT', $vars );
	}
	
	/**
	 * @method delete
	 * @param array $vars Request parameters.
	 * @return HttpRequest_Response_Html
	 */
	public function delete($vars = array())
	{
		return $this->request ( 'DELETE', $vars );
	}
	
	/**
	 * @method head
	 * @param array $vars Request parameters.
	 * @return HttpRequest_Response_Html
	 */
	public function head($vars = array())
	{
		return $this->request ( 'HEAD', $vars );
	}


	/**
	 * @method __destruct   Destructor
	 */
	public function __destruct()
	{
		parent::__destruct();
	}
}
