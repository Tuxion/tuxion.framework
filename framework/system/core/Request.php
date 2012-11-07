<?php namespace core;

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
    
    //Set the request-method.
    switch(tx('Server')->request_method){
      case 'GET': $this->method = GET; break;
      case 'POST': $this->method = POST; break;
      case 'PUT': $this->method = PUT; break;
      case 'DELETE': $this->method = DELETE; break;
      default: throw new \exception\Unexpected(
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
    
    //Determine the input data based on the given content_type and request method.
    if(tx('Server')->offsetExists('http_content_type') && !empty(tx('Server')->http_content_type))
    {
      
      if(substr_count(tx('Server')->http_content_type, ';') > 0){
        $type = trim(
          substr(
            tx('Server')->http_content_type,
            0,
            strpos(tx('Server')->http_content_type, ';')
          )
        );
      }
      
      else{
        $type = trim(tx('Server')->http_content_type);
      }
      
      #TODO: Use proper data pipes.
      //Do different things with different types.
      switch($type)
      {
        
        //Form data will be URL-encoded, and in a POST request, PHP will already have decoded that for us.
        case 'application/x-www-form-urlencoded':
        case 'multipart/form-data':
          if($this->method(POST)){
            $data = $_POST;
          }else{
            $data = parse_string(file_get_contents('php://input'));
          }
          break;
        
        //We will parse JSON.
        case 'application/json':
          $data = json_decode(file_get_contents('php://input'));
          break;
          
        //We will parse XML, or will we?
        //http://gaarf.info/2009/08/13/xml-string-to-php-array/
        // case 'application/xml':
        //   $data = (file_get_contents('php://input'));
        //   break;
        
        //We do not support other content types.
        default:
          throw new \exception\Unexpected('Unsupported content-type "%s" sent to server.', $type);
          break;
          
      }
      
    }
    
    //Do an educated guess.
    else{
      #TODO: Do an educated guess.
      $data = $_GET;
    }
    
    //Set the data.
    $this->data = Data($data);
    
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
    $this->url = \classes\Url::create("$scheme://$server$port$req_uri", true, false);
    
    //Enter a log entry.
    tx('Log')->message($this, 'Request class initialized.');
    
  }
  
  //Return the request method, or true if the method is in the given method.
  public function method($in=null)
  {
    
    if(is_null($in)){
      return $this->method;
    }
    
    if(!is_int($in)){
      throw new \exception\InvalidArgument('Expecting $in to be integer. %s given', ucfirst(typeof($in)));
    }
    
    return checkbit($this->method, $in);
    
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
