<?php

define('PARSE_APPLICATION_ID', '<your app id>');
define('PARSE_APP_MASTERKEY', '<your master key>');

//https://www.parse.com/docs/rest
class ParseClient 
{
	const url = 'https://api.parse.com/1/classes';
	const QUERY = 0;
	
	function ParseClient()
	{

	}
	
	function getObjectsByClass($classname, $query=null)
	{
		$url = ParseClient::url  .  '/'. $classname;
		$c = $this->_getCurl();
		curl_setopt($c, CURLOPT_URL, $url);
		if($query != null )
		{
			$postdata = 'where='. json_encode( $query );
			curl_setopt($c, CURLOPT_POSTFIELDS, $postdata );
			curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'GET');
		}
		$response = $this->_getResponse($c);
		$json = $this->_convertResponse($response);
		return $json->results;
	}
	
	function updateObject($classname, $obj)
	{
		$id = $obj->objectId;
		unset($obj->objectId);
		unset($obj->createdAt);
		unset($obj->updatedAt);
		$url = ParseClient::url  .'/' .$classname. '/' . $id;
		$c = $this->_getCurl();
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PUT'); # for PUT DELETE requests
		curl_setopt($c, CURLOPT_URL, $url);
		$postdata = json_encode( $obj );	
		curl_setopt($c, CURLOPT_POSTFIELDS, $postdata );
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		
		$response = curl_exec($c);
		$json = $this->_convertResponse($response);
		if( !property_exists($json, 'updatedAt')  || !isset($json->updatedAt) )
			die('error object was not updated, response: '.$response);
		return $response;
	}
	
	function _getCurl()
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_TIMEOUT, 15);
		curl_setopt($c, CURLOPT_USERAGENT, 'PHP Parse Client/0.1');
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERPWD, PARSE_APPLICATION_ID . ':' . PARSE_APP_MASTERKEY);
		return $c;
	}
	function _getResponse($c)
	{
		$response = curl_exec($c);
		$statusCode= $this->_getStatusCode($c);
		if($statusCode != '200')
			die($statusCode . ' : ' .$response);
		return $response;
	}
	
	function _getStatusCode($curl)
	{
		return curl_getinfo($curl, CURLINFO_HTTP_CODE);
	}
	
	function _convertResponse($response)
	{
		return json_decode($response);
	}
}

?>