<?php namespace core;

use \classes\Url;
use \classes\OutputData;

class Request
{
  
  //Private properties.
  private
    $method = -1;
  
  //Public properties.
  public
    $data = null,
    $url = null,
    $accept = [];
  
  //The init method fills the data based on the request method.
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'Request class initializing.');
    
    //Get the request URI.
    $req_uri = (isset(tx('Server')->request_uri) ? tx('Server')->request_uri : tx('Server')->php_self);

    //Get the host.
    $server = tx('Server')->server_name;

    //Is it a secure scheme?
    $secure = (isset(tx('Server')->https) && (tx('Server')->https == 'on'));

    //Get the scheme.
    $scheme = strstr(strtolower(tx('Server')->server_protocol), '/', true) . ($secure ? 's' : '');

    //Get the port.
    $port = ((tx('Server')->server_port == 80) ? '' : (':'.tx('Server')->server_port));
    
    //Set the URL.
    $this->url = Url::create("$scheme://$server$port$req_uri", true, false);
    
    //Set the request-method.
    switch(tx('Server')->request_method){
      case 'GET': $this->method = GET; break;
      case 'POST': $this->method = POST; break;
      case 'PUT': $this->method = PUT; break;
      case 'DELETE': $this->method = DELETE; break;
      default: throw new \exception\BadRequest(
        'Unsupported request method: %s.',
        tx('Server')->request_method
      );
    }
    
    //Store the accept headers.
    $this->accept = [
      'mimes' => $this->_normalizeAcceptHeader('http_accept'),
      'charset' => $this->_normalizeAcceptHeader('http_accept_charset'),
      'encoding' => $this->_normalizeAcceptHeader('http_accept_encoding'),
      'language' => $this->_normalizeAcceptHeader('http_accept_language')
    ];
    
    //Get the input body from the URL.
    if($this->method(GET)){
      $input = new OutputData(
        $this->url->segments->arrayExists('query')
        ? $this->url->segments->query
        : ''
      );
    }
    
    //Get the input body from the request body.
    else{
      $input = new OutputData(file_get_contents('php://input'));
    }
    
    //Get the class name to the standard node data type. All given input must be convert-able using this.
    $standard_class = tx('Outputting')->loadStandardClass('nodes');
    
    //Create a converter instance based on the content type header.
    if(tx('Server')->offsetExists('http_content_type') && !empty(tx('Server')->http_content_type))
    {
      
      //Cut the part behind the ";" off?
      if(substr_count(tx('Server')->http_content_type, ';') > 0){
        $type = trim(explode(';', tx('Server')->http_content_type)[0]);
      }
      
      //Just use the whole thing.
      else{
        $type = trim(tx('Server')->http_content_type);
      }
      
      //Try to load the converter for the given input.
      try{
        $converter_class = $standard_class::loadConverterByMime($type);
      }
      
      //Catch the exception thrown when the converter does not exist.
      catch(\exception\ResourceMissing $e)
      {
        
        //Create a better exception.
        $new = new \exception\BadRequest('Unsupported content-type "%s" sent to server.', $type);
        
        //Set the previous exception.
        $new->setPrev($e);
        
        //Throw the new exception.
        throw $new;
        
      }
      
      //Create the converter instance.
      $converter = new $converter_class($input);
      
    }
    
    //Do an educated guess as to which converter to load.
    else
    {
      
      //Get the converter class.
      $converter_class = $standard_class::loadConverter('FormData');
      
      //Create the converter.
      $converter = new $converter_class($input);
      
    }
    
    //Convert!
    $data = $converter->standardize();
    
    //Set the data.
    $this->data = $data;
    
    //Enter a log entry.
    tx('Log')->message($this, 'Request class initialized.');
    
  }
  
  //Return the request method, or true if the method is in the given method.
  public function method($in=null)
  {
    
    if(is_null($in)){
      return $this->method;
    }
    
    return wrap($in)->hasBit($this->method)->isTrue();
    
  }
  
  //Normalizes the accept header with the given name, and returns an array with meta-data.
  private function _normalizeAcceptHeader($header_name)
  {
    
    //Check if the header was sent.
    if(!tx('Server')->offsetExists($header_name)){
      return [];
    }
    
    //Explode to get an array of different options.
    $options = explode(',', tx('Server')->{$header_name});
    $return = [];
    
    //Break up the accept header into an easy-to-read array.
    foreach($options as $i => $option)
    {
      
      //Explode the options into an array of parameters.
      $params = explode(';', $option);
      
      //First parameter is always the value.
      $return[$i]['value'] = array_shift($params);
      $return[$i]['params'] = [];
      
      //Parse the remaining parameters.
      foreach($params as $param){
        list($key, $value) = explode('=', $param);
        $return[$i]['params'][$key] = $value;
      }
      
    }
    
    //Use the relevance parameter to sort the array.
    uasort($return, function($a, $b){
      
      //$a Has a relevance parameter, but $b doesn't. A is more relevant.
      if(array_key_exists('q', $a['params']) && !array_key_exists('q', $b['params'])){
        return -1;
      }
      
      //$b Has a relevance parameter, but $a doesn't. B is more relevant.
      elseif(!array_key_exists('q', $a['params']) && array_key_exists('q', $b['params'])){
        return 1;
      }
      
      //If they both have relevance parameters, we should compare.
      elseif(array_key_exists('q', $a['params']) && array_key_exists('q', $b['params']))
      {
        
        //Less relevant?
        if($a['params']['q'] < $b['params']['q']){
          return 1;
        }
        
        //More relevant?
        elseif($a['params']['q'] > $b['params']['q']){
          return -1;
        }
           
      }
      
      //Equally relevant.
      return 0;
      
    });
    
    //Return the normalized accept header.
    return $return;
    
  }
  
}
