<?php

/**
 * Geoserver API Class
 *
 *
 * @package    Fiber Management System
 * @author     Gregory Brewster <gbrewster@agoasite.com>
 * @copyright  (C)2016 City of Sandy
 *
 *
 */

class geoserver {
	
	/**
	 * Make sure variables are private!
	 */
	private $geoserver_address;
	private $geoserver_username;
	private $geoserver_password;
	
	/**
	 * Declares the default constructor
	 * Upon creation, the object will have the following variables set with information
	 * obtained from the parameters defined by new
	 */
	public function __construct($user, $pass, $address) {
		$this->geoserver_address = $address;
		$this->geoserver_username = $user;
		$this->geoserver_password = $pass;
	}
	
/**********************************************************************************************************************************
 ** GEOSERVER ACTION METHODS GEOSERVER ACTION METHODS GEOSERVER ACTION METHODS GEOSERVER ACTION METHODS GEOSERVER ACTION METHODS **
 **********************************************************************************************************************************/ 
 
	/**
	 *  Function will send a put request
	 *  Params: array of arguments, key=>value, uri and format
	 *  Returns: Array, "errors", "result" 
	 */
	public function put_call($arguments, $uri, $format, $content_type = "application/json"){
		if($format == "json"){
			$record = $this->query_put("/rest/$uri.$format", json_encode($arguments), $content_type);
		} else {
			$record = $this->query_put("/rest/$uri", $arguments, $content_type);
		}
		
		$record->error = $this->get_status_code($record->info);
		
		return $record;
	}

	/**
	 *  Function will send a post request
	 *  Params: array of arguments, key=>value, uri and format
	 *  Returns: Array, "errors", "result" 
	 */
	public function post_call($arguments, $uri, $format){
		$record = $this->query_post("/rest/$uri.$format", json_encode($arguments));
		$record->error = $this->get_status_code($record->info);
		
		return $record;
	}
	
	/**
	 *  Function will send a get request
	 *  Params: array of arguments, key=>value, uri and format
	 *  Returns: Array, "errors", "result" 
	 */
	public function get_call($arguments, $uri, $format){
		$record = $this->query_get("/rest/$uri.$format", $arguments);
		$record->error = $this->get_status_code($record->info);
		
		if(isset($record) && isset($record->result)){
			if(count($record->result) == 1){
				$record->result = array($record->result);
			}
		}
		
		return $record;
	}
	
	/**
	 *  Function will send a delete request
	 *  Params: array of arguments, key=>value, uri and format
	 *  Returns: Array, "errors", "result" 
	 */
	public function delete_call($arguments, $uri, $format){
		$record = $this->query_delete("/rest/$uri.$format", $arguments);
		$record->error = $this->get_status_code($record->info);
		return $record;
	}
	

	
/*****************************************************************************************************************************
 ** CURL METHODS   CURL METHODS   CURL METHODS   CURL METHODS   CURL METHODS   CURL METHODS   CURL METHODS   CURL METHODS ****
 ****************************************************************************************************************************/ 
	
	
	/**
	 *  Function will query geoserver using the given parameters
	 *  Params: URI without host, json_encoded string of arguments
	 *  Returns: Array, "errors", "result" 
	 */
	protected function query_post($uri, $arguments){
		$data = (object) array();
		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->geoserver_address.$uri,
			CURLOPT_CUSTOMREQUEST=> "POST",
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_TIMEOUT => 5,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERPWD => "$this->geoserver_username:$this->geoserver_password",
			CURLOPT_POSTFIELDS => $arguments,
			CURLOPT_HTTPHEADER => array(
									"Content-Type: application/json"
								)
			//CURLOPT_VERBOSE => 1,				//debugging
		);
		
		// Set options against curl object
		curl_setopt_array($ch, $options);
		$file = curl_exec($ch);
		if(curl_error($ch)){
			$err = curl_error($ch);
		} else {
			$err = null;
		}
		
		$data->error = $err;
		$data->info = curl_getinfo($ch);
		
		$result_obj = json_decode($file);
		if($result_obj){
			if(count($result_obj) > 1){
				$data->result = $result_obj;
			} else {
				$data->result = array($result_obj);
			}
		} else {
			$data->result = array((object) array("response"=>$file));
		}

		
		// close curl object after you check for errors!
		curl_close($ch);
		
		return $data;
	}
	
	/**
	 *  Function will query geoserver using the given parameters
	 *  Params: URI without host, json_encoded string of arguments
	 *  Returns: Array, "errors", "result" 
	 */
	protected function query_put($uri, $arguments, $content_type = "application/json" ){
		$data = (object) array();
		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->geoserver_address.$uri,
			CURLOPT_CUSTOMREQUEST=> "PUT",
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERPWD => "$this->geoserver_username:$this->geoserver_password",
			CURLOPT_POSTFIELDS => $arguments,
			CURLOPT_HTTPHEADER => array(
									"Content-Type: $content_type"
								),
			//CURLOPT_VERBOSE => 1,				//debugging
		);
		
		// Set options against curl object
		curl_setopt_array($ch, $options);
		$file = curl_exec($ch);
		
		if(curl_error($ch)){
			$err = curl_error($ch);
		} else {
			$err = null;
		}
		
		$data->error = $err;
		$data->info = curl_getinfo($ch);
		
		$result_obj = json_decode($file);
		if($result_obj){
			if(count($result_obj) > 1){
				$data->result = $result_obj;
			} else {
				$data->result = array($result_obj);
			}
		} else {
			$data->result = array((object) array("response"=>$file));
		}
		
		// close curl object after you check for errors!
		curl_close($ch);
		
		return $data;
	}
	
	/**
	 *  Function will query geoserver using the given parameters
	 *  Params: URI without host, array of arguments, key=>value
	 *  Returns: Array, "errors", "result" 
	 */
	protected function query_get($uri, $arguments){
		$argument_str = "";
		if(isset($arguments) && count($arguments) > 0){
			$argument_str .= "?";
			foreach($arguments as $arg_key=>$arg_val){
				$argument_str.= $arg_key."=".$arg_val."&";
			}
		}
		
		$data = (object) array();
		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->geoserver_address.$uri,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTP_VERSION => 2,
			CURLOPT_USERPWD => "$this->geoserver_username:$this->geoserver_password",
			CURLOPT_HTTPHEADER => array(
									"Content-Type: application/json"
									),
			//CURLOPT_VERBOSE => 1,				//debugging
		);
		
		// Set options against curl object
		curl_setopt_array($ch, $options);
		$file = curl_exec($ch);
		
		if(curl_error($ch)){
			$err = curl_error($ch);
		} else {
			$err = null;
		}
		
		$data->error = $err;
		$data->info = curl_getinfo($ch);
		
		$result_obj = json_decode($file);
		if($result_obj){

			$data->result = $result_obj;
		} else {
			$data->result = null;
		}
		
		// close curl object after you check for errors!
		curl_close($ch);
		
		return $data;
	}
	
	/**
	 *  Function will query geoserver using the given parameters
	 *  Params: URI without host
	 *  Returns: Array, "errors", "result" 
	 */
	protected function query_delete($uri, $arguments = array()){
		
		$argument_str = "";
		if(isset($arguments) && count($arguments) > 0){
			$argument_str .= "?";
			foreach($arguments as $arg_key=>$arg_val){
				$argument_str.= $arg_key."=".$arg_val."&";
			}
		}
		
		$data = (object) array();
		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->geoserver_address.$uri."/".$argument_str,
			CURLOPT_CUSTOMREQUEST=> "DELETE",
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERPWD => "$this->geoserver_username:$this->geoserver_password",
			CURLOPT_POSTFIELDS => $arguments,
			CURLOPT_HTTPHEADER => array(
									"Content-Type: application/json"
								),
			//CURLOPT_VERBOSE => 1,				//debugging
		);
		
		// Set options against curl object
		curl_setopt_array($ch, $options);
		$file = curl_exec($ch);
		
		if(curl_error($ch)){
			$err = curl_error($ch);
		} else {
			$err = null;
		}
		
		$data->error = $err;
		$data->info = curl_getinfo($ch);
		
		$result_obj = json_decode($file);
		if($result_obj){
			if(count($result_obj) > 1){
				$data->result = $result_obj;
			} else {
				$data->result = array($result_obj);
			}
		} else {
			$data->result = null;
		}
		
		// close curl object after you check for errors!
		curl_close($ch);
		
		return $data;
	}
	
	
	protected function get_status_code($info){
		if($info['http_code'] == 200){
			return null;
		} else if($info['http_code'] == 201){
			return null;
		} else if($info['http_code'] == 403){
			return "403 - Forbidden";
		} else if($info['http_code'] == 404){
			return "404 - Not Found";
		} else if($info['http_code'] == 405){
			return "405 - Method Not Allowed";
		} else if($info['http_code'] == 500){
			return "500 - Internal Server Error";
		} else {
			return "Unknown";
		}
	}
}
?>