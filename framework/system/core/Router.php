<?php namespace core;

class Router
{
  
  //Private properties.
  private
    $state=0,
    $history=[],
    $fututre=[];
  
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
    
    if(strlen($path) > strlen(tx('Config')->urls->path)+2)
    {
    
      //Bite off the system base (the system base length + 2 for the leading and trailing slashes).
      $this->path = $start = substr($path, strlen(tx('Config')->urls->path)+2);
      
      //Clean the request path.
      $this->path = $this->cleanPath($this->path);
      
      //Redirect if this changed the path at all.
      if($this->path !== $start){
        $this->redirect(url("/{$this->path}"));
        return $this->_handleRedirect();
      }
    
    }
    
    else{
      $this->path = '';
    }
    
    //Split the path into segments, and put them in the future.
    $this->future = explode('/', $this->path);
    
    //Route!
    $this->_route();
    
    //Enter a log entry.
    tx('Log')->message(__CLASS__, 'class initialize', 'Router class initialized.');
    
    exit;
    
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
        
        //Trim off the illegal characters off the end and start.
        $segment = trim($segment, ' ');
        
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
  
  //Reroutes to a new path starting from the current segment.
  public function reroute($path, $prepend=false)
  {
    
    //Our own little history, like a diary! :)
    static $history = [];
    
    //Detect cyclic reference.
    if(in_array($path, $history)){
      throw new \exception\Configuration(
        'Cyclic reference occurred: The "%s" path came back to itself after %s reroutings.',
        $path, count(array_slice($history, array_search($path, $history)))-1
      );
    }
    
    //Prepare some variables.
    $path = $this->cleanPath($path);
    $segments = explode('/', $path);
    
    //Change the future.
    if($prepend)
    {
      
      $segments = array_reverse($segments);
      
      foreach($segments as $segment){
        array_unshift($this->future, $segment);
      }
      
    }
    
    //Create the future.
    else{
      $this->future = $segments;
    }
    
    //Remove the last path from the history.
    array_pop($this->history);
    
    //Add this path to our diary.
    $history[] = implode('/', $this->history)."/$path";
    
    //Build the new path.
    $this->path = implode('/', $this->history).'/'.implode('/', $this->future);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Route to the right endpoint based on given path.
  private function _route()
  {
    
    //Iterate over the route segments that are in the future.
    while(true)
    {
    
      //Which state are we in?
      switch($this->state)
      {
        
        //The first segment.
        case 0:
          
          //We are at the first segment, yet we don't have a future. We will use this as alias.
          if(count($this->future) == 0){
            $this->state = 3;
          }
          
          //The first segment is the reserved "com". Means we will route to a component.
          elseif($this->future[0] == 'com'){
            $this->history[] = array_shift($this->future);
            $this->state = 1;
          }
          
          //The first segment is the reserved "sys". Means we will route to a system utility.
          elseif($this->future[0] == 'sys'){
            $this->history[] = array_shift($this->future);
            $this->state = 2;
          }
          
          //The first segment is unreserved. We will use it as alias.
          else{
            $this->state = 3;
          }
          
          $path = implode('/', $this->history);
          
          break;
          
        //Route to a component.
        case 1:
          
          //Check if we have a component name available.
          if(empty($this->future)){
            throw new \exception\NotFound('Just "/com" will lead to nowhere. Ever.');
          }
          
          //Get the component name and route there.
          $this->history[] = $component_name = array_shift($this->future);
          $path = implode('/', $this->history);
          $this->_routeComponent($component_name);
          
          break;
          
        //Simple route progression.
        case 4:
          
          if(empty($this->future)){
            break 2;
          }else{
            $this->history[] = array_shift($this->future);
          }
          
          $path = implode('/', $this->history);
          
          break;
        
        //Route to a system utility.
        case 2:
          break 2;
        
        //Reroute based on an alias.
        case 3:
          
          //Get the alias.
          $this->history[] = $alias = array_shift($this->future);
          $path = implode('/', $this->history);
          $this->_routeAlias($alias);
          
          break;
          
      }
      
      tx('Log')->message($path);//.BR;
      
      //Call preprocessors.
      foreach(\classes\Router::routes(tx('Request')->method(), $path) as $key => $route){
        $route->_callPres(tx('Request')->data());
      }
      
    }
    
    return $this;
    
  }
  
  //Route to components.
  private function _routeComponent($component_name)
  {
    
    //Are we allowed to use numeric component identifiers?
    if(is_numeric($component_name) && tx('Config')->config->route_allow_numeric_components !== true){
      throw new \exception\NotFound('"%s" Is not a valid component.', $component_name);
    }
    
    //Get component info.    
    $cinfo = tx('Component')[$component_name];
    
    //Are we using a numeric identifier? If so, we reroute.
    if(is_numeric($component_name)){
      $this->reroute($cinfo->name, true);
      $this->state = 1;
      return;
    }
    
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
    
    //Everything OK! Progress the state simple route progression.
    $this->state = 4;
    
  }
  
  //Reroute based on an alias.
  private function _routeAlias($alias)
  {
    
    //Get the alias from the database.
    $result = tx('Sql')->query('SELECT `value` FROM `#system_route_aliases` WHERE `key` = ?s', $alias);
    
    //Check if it wasn't found.
    if($result->count() == 0){
      throw new \exception\NotFound('"%s" Is not a valid alias.', $alias);
    }
    
    //Reroute to the alias.
    $this->reroute($result[0]->value, true);
    $this->state = 0;
    
  }
  
  //Returns true if the given path matches the current request path.
  public function matchPath()
  {
    
    $this->_handleArguments(func_get_args(), $type, $path);
    
    //Test for type?
    if(!is_null($type))
    {
      
      //See if the type matches the current request type.
      if(!tx('Request')->method($type)){
        return false;
      }
      
    }
    
    //Test for path? If not we are done.
    if(is_null($path)){
      return true;
    }
    
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
  
  //Accepts an array with up to 2 arguments and returns an array with keys "type" and "path".
  public function _handleArguments($args, &$type=null, &$path=null)
  {
    
    //Handle arguments.
    if(count($args) == 2){
      $type = $args[0];
      $path = $args[1];
    }
    
    //Only one argument has been given.
    elseif(count($args) == 1)
    {
      
      //Is it the type?
      if(is_int($args[0])){
        $type = $args[0];
        $path = null;
      }
      
      //Is it the path?
      elseif(is_string($args[0])){
        $type = null;
        $path = $args[0];
      }
      
      //It is NOTHING!
      else{
        throw new \exception\InvalidArgument('Expecting a string or integer. %s given.', ucfirst(typeof($args[0])));
      }
      
    }
    
    //An invalid amount of arguments were given.
    else{
      throw new \exception\InvalidArgument('Expecting one or two arguments. %s Given.', count($args));
    }
    
    return ['type' => $type, 'path' => $path];
    
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
