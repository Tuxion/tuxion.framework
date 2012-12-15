<?php namespace classes;

abstract class BaseTemplator
{
  
  //Static private properties.
  static protected
    $data_type;
  
  //Public properties.
  protected
    $data,
    $headers=[],
    $materials;
  
  //Set the data.
  public function __construct(BaseStandardData $data, Materials $materials)
  {
    
    $this->data = $data;
    $this->materials = $materials;
    
  }
  
  //Custom getters.
  public function __get($key)
  {
    
    //The custom getter must exist at this point.
    if(!method_exists($this, "get_$key")){
      throw new \exception\NotImplemented(
        'Property "%s" does not exist and does not have custom getter (%s).',
        $key, "get_$key"
      );
    }
    
    //Forward the call.
    return call_user_func_array([$this, "get_$key"]);
    
  }
  
  //Returns the raw data.
  public function get()
  {
    
    return $this->data->raw();
    
  }
  
  //Return the standard data in this template as given type.
  public function to($type)
  {
    
    //Find the class.
    $class = "\\outputting\\".static::$data_type."\\converters\\$type";
    
    //Converting action!
    return (new $class)->to($this->data);
    
  }
  
  //Returns the website title.
  public function getTitle()
  {
    
    return tx('Config')->config->title;
    
  }
  
  //Set the headers to use when this template needs to be output to the stream.
  public function setHeaders(array $headers)
  {
    
    $this->headers = array_merge($this->headers, $headers);
    
  }
  
  //Returns the headers.
  public function getHeaders()
  {
    
    return $this->headers;
    
  }
  
  //Do a request to a different route from within the template.
  public function request($path, $data = null)
  {
    
    //Try doing the request.
    try{
      return tx('Response')->outputRoute(
        $this->materials->router->type,
        $path,
        (is_null($data) ? new \outputting\nodes\Standard([]) : $data),
        $this->materials->mime,
        true,
        false
      )->data;
    }
    
    //If something broke, we will return an error message.
    catch(\exception\Exception $e)
    {
      
      return sprintf('Failed to load "%s": %s', $path, $e->getMessage());
      
    }
    
  }
  
}
