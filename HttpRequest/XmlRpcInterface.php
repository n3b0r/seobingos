<?php

/**
 * 
 * @version 1.0
 * @author Dinahosting
 */
interface HttpRequest_XmlRpcInterface
{
	public function sendRequest($remoteMethod, array $params);
	
	public function setAuthentication($username, $password, $authType = self::HTTP_AUTH_BASIC);
}