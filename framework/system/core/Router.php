<?php namespace core;

class Router
{
  
  //Private properties.
  private
    $routes=[];
  
  //Public properties.
  public
    $path,
    $redirect_url=false;
  
  //When we initiate, we will clean the request path and load the defined routes.
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message(__CLASS__, 'class initialize', 'Router class initializing.');
    
    //Get the path.
    $path = tx('Request')->url->segments->path;
    
    //Bite off the system base.
    $path = $this->path = $start = trim(substr($path, strlen(tx('Config')->urls->path)+1), '/ ');
    
    //Clean the request path.
    $path = $this->cleanPath($path);
    
    //Redirect if this changed the path at all.
    if($path !== $start){
      $this->redirect(url("/$path"));
      return $this->_handleRedirect();
    }
    
    //Route!
    set_exception_handler([$this, '_handleException']);
    $this->_route();
    
    //Enter a log entry.
    tx('Log')->message(__CLASS__, 'class initialize', 'Router class initialized.');
    
  }
  
  //Cleans up the request-URI-path in order for it to match our routing systems.
  public function cleanPath($path)
  {
    
    //OK!
    if($path === ''){
      return $path;
    }
    
    //Do the following.
    do{
    
      //Remember what the path was like before we started mangling it.
      $start = $path;
    
      //Decode.
      $path = urldecode($path);
  
      //Replace backward slashes.
      $path = str_replace('\\', '/', $path);
      
      //Trim double slashes.
      $path = preg_replace('~/+~', '/', $path);
      
      //Replace /../ stuff.
      $path = preg_replace('~(?=\/)\.\.*/~', './', $path);
      
      //Replace spaces.
      $path = str_replace(' ', '+', $path);
      
      //Replace illegal characters.
      $path = preg_replace('~[#@?!]~', '-', $path);
      
      //Explode into segments.
      $segments = explode('/', $path);
      
      //Used to detect the endpoint.
      $endpoint = false;
      
      //Validate and normalize segments.
      foreach(array_keys($segments) as $key)
      {
        
        //Keep a reference.
        $segment =& $segments[$key];
        
        //Have we met our end yet?
        if($endpoint){
          unset($segments[$key], $segment);
          continue;
        }
        
        //Trim off the illegal characters off the end.
        $segment = preg_replace('~[\.]+$~', '', $segment);
        
        //Detect premature endpoints.
        if(strpos($segment, '.')){
          $endpoint = true;
        }
        
        //Unset if empty.
        if(empty($segment)){
          unset($segments[$key]);
        }
        
        //Unset the reference.
        unset($segment);
        
      }
      
      //Use the normalized segments as path.
      $path = implode('/', $segments);
      
    }
    
    //And keep repeating it as long as it is still changing stuff.
    while($path !== $start);
    
    //Return the new path.
    return $path;
    
  }
  
  //Route to the right endpoint based on given path.
  private function _route(&$history=[])
  {
    
    //Get the path.
    $path = $this->path;
    
    //Split the route into segments.
    $segments = explode('/', $path);
    
    //Create a "route" out of every segment.
    foreach(array_keys($segments) as $key)
    {
      
      //Keep a reference to the segment.
      $segment =& $segments[$key];
      
      //Add the previous segment.
      $segment =
        ($key > 0 ? $segments[$key-1] : '').
        ($key > 0 ? "/$segment" : "$segment");
      
      //Unset the reference.
      unset($segment);
      
    }
    
    //Check if segmentZERO is amongst our reserved special segments.
    switch($segments[0])
    {
      
      //Component.
      case 'com':
        
        //No component identifier?
        if(count($segments) == 1){
          throw new \exception\NotFound('Just /com leads to nothing. Ever.');
        }
        
        //Get the component name.
        $tmp = explode('/', $segments[1]);
        
        //Still no component identifier? :(
        if(count($tmp) == 1){
          throw new \exception\NotFound('Just /com leads to nothing. Ever.');
        }
        
        //The component name.
        $component_name = $tmp[1];
        
        //Route to the component.
        return $this->routeComponent($component_name, $segments);
      
      //System.
      case 'sys':
        break;
      
      //Alias.
      default:
      
        //Get the alias.
        $alias = array_shift($segments);
        
        //Detect cyclic reference.
        if(in_array($alias, $history)){
          throw new \exception\Configuration('Cyclic reference occurred: The "%s" alias came back to itself after %s reference(s).', $alias, count(array_slice($history, array_search($alias, $history)))-1);
        }
        
        //Store this step in the history books.
        $history[] = $alias;
        
        //Get the alias from the database.
        $result = tx('Sql')->query('SELECT `value` FROM `#system_route_aliases` WHERE `key` = ?s', $alias);
        
        //Check if it wasn't found.
        if($result->count() == 0){
          throw new \exception\NotFound('"%s" Is not a valid alias.', $alias);
        }
        
        //Reroute to the alias.
        $this->path = $result[0]->value.substr(end($segments), strlen($alias));
        return $this->_route($history);
        
    }
    
  }
  
  //Route to components.
  private function routeComponent($component_name, array $segments)
  {
    
    //Are we allowed to use numeric component identifiers?
    if(is_numeric($component_name) && tx('Config')->config->route_allow_numeric_components !== true){
      throw new \exception\NotFound('"%s" Is not a valid component.', $component_name);
    }
    
    //Get component info.    
    $cinfo = tx('Component')[$component_name];
    
    //Get all other relevant components.
    $components = tx('Sql')->query('
      SELECT `c`.*
      FROM `#system_components` AS `c`
      INNER JOIN `#system_component_extensions` AS `ce` ON `c`.`id` = `ce`.`extended_by_id`
      WHERE `ce`.`component_id` = ?i',
      $cinfo->id
    )
    
    //Get an array of only their names.
    ->map(function($row){
      return $row->name;
    })
    
    //Add our main component to the mix.
    ->push($cinfo->name);
    
    //Load all of their controllers.
    foreach($components as $component){
      tx('Component')[$component]->loadControllers();
    }
    
    //Return.. ehm... true!
    return true;
    
  }
  
  //Returns true if the given path matches the current request path.
  public function matchPath($path)
  {
    
    $current_segments = explode('/', $this->path);
    $given_segments = explode('/', $this->cleanPath($path));
    
    foreach($given_segments as $i => $segment)
    {
      
      //If we are longer than the current path. It is always false.
      if(!array_key_exists($i, $current_segments)){
        return false;
      }
      
      //If we start with $, we are always a match.
      if($segment{0} === '$'){
        continue;
      }
      
      //Match the segment.
      if($segment !== $current_segments[$i]){
        return false;
      }
      
    }
    
    return true;
    
  }
  
  //Set the URL to redirect to.
  public function redirect($url)
  {
    
    //Validate $url.
    if(!($url instanceof \classes\Url)){
      throw new \exception\InvalidArgument('Expecting $url to be an instance of \\classes\\Url. %s given.', ucfirst(typeof($url)));
    }
    
    //Set the redirect URL.
    $this->redirect_url = $url;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Returns true if we redirected, or false otherwise.
  public function redirected()
  {
    
    return ($this->redirect_url instanceof \classes\Url);
    
  }
  
  //Handles exceptions that are encountered during the routing process.
  public function _handleException($e)
  {
    
    tx('Debug')->exceptionHandler($e);
    
  }
  
  //What to do when we have set a redirect.
  private function _handleRedirect()
  {
    
    if(!$this->redirected()){
      return $this;
    }
    
    header('Location: '.$this->redirect_url);
    
    return $this;
    
  }

}
