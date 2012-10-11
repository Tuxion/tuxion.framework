<?php namespace classes;

class Router
{
  
  //Cleans up the request-URI-path in order for it to match our routing systems.
  public static function cleanPath($path)
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
      $path = preg_replace('~[#@?!]+~', '-', $path);
      
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
        $segment = trim($segment, '+');
        
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
  
  //Accepts an array with up to 2 arguments and returns an array with keys "type" and "path".
  public static function handleArguments($args, &$type=null, &$path=null)
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
        throw new \exception\InvalidArgument(
          'Expecting a string or integer. %s given.',
          ucfirst(typeof($args[0]))
        );
      }
      
    }
    
    //An invalid amount of arguments were given.
    else{
      throw new \exception\InvalidArgument('Expecting one or two arguments. %s Given.', count($args));
    }
    
    return ['type' => $type, 'path' => $path];
    
  }
  
  //Accepts 2 paths, one containing keys like: "path/$name", and one containing values like: "path/Avaq".
  public static function matchPath($keys, $values)
  {
    
    
    //Prepare variables.
    $keys = explode('/', self::cleanPath($keys));
    $values = explode('/', self::cleanPath($values));
    $parameters = [];
    
    //If the output type is neutral, don't compare it.
    if(substr_count(end($keys), '.') === 0 && substr_count(end($values), '.') > 0){
      $trimmed = strstr(array_pop($values), '.', true);
      array_push($values, $trimmed);
    }
    
    //Don't mind the .parts.
    $values[count($values)-1] = str_replace('.part', '', end($values));
    
    //Make sure nothing goes weird.
    reset($keys);
    reset($values);
    
    //If there are not enough values for the keys, it is not a match.
    if(count($values) < count($keys)){
      return false;
    }
    
    //Validate segments and find parameters.
    while( (list(,$key) = each($keys)) && (list(,$value) = each($values)) )
    {
      
      //Split on file extension.
      @list($key,$kext) = explode('.', $key);
      @list($value,$vext) = explode('.', $value);
      
      if($kext && $vext && tx('Mime')->getMime($kext) !== tx('Mime')->getMime($vext)){
        return false;
      }
      
      //Are we dealing with a parameter?
      if($key{0} == '$')
      {
        
        //Are we dealing with a character class?
        if($key{1} == '['
        && substr($key, -1) == ']'
        && substr_count($key, '[') == 1
        && substr_count($key, ']') == 1
        ){
          
          //Test is the value matches the character class.
          if(preg_match('~^'.substr($key, 1).'+$~', $value) !== 1){
            return false;
          }
          
        }
        
        //Are we dealing with a data-preset?
        switch(substr($key, 1))
        {
          
          case 'int':
            if(preg_match('~^[0-9]+$~', $value) !== 1){
              return false;
            }
          break;
            
          case 'float':
            if(preg_match('~^[0-9]+\.[0-9]+$~', $value) !== 1){
              return false;
            }
          break;
          
          case 'word':
            if(preg_match('~^\w+$~', $value) !== 1){
              return false;
            }
          break;
          
        }
        
        //Add the value to our parameters.
        $parameters[substr($key, 1)] = $value;
        
      }
      
      //If not, both segments must be the same, or it won't be a match.
      elseif($key !== $value){
        return false;
      }
      
    }
    
    //Return the array of parameters if everything was matched.
    return $parameters;
    
  }
  
  //Private properties.
  private
    $state=0,
    $history=[],
    $fututre=[],
    $controllers=[];
  
  //Public properties.
  public
    $type,
    $input,
    $output,
    $inner_template,
    $outer_template,
    $path,
    $endpoint;
  
  //The constructor will set some properties.
  public function __construct($type, $path, DataBranch $input)
  {
    
    //Set the request method.
    $this->type = $type;
    
    //Clean the path.
    $this->path = self::cleanPath($path);
    
    //Split the path into segments, and put them in the future.
    $this->future = explode('/', $this->path);
    
    //Set the input.
    $this->input = $input;
    
    //Set the output.
    $this->output = Data([]);
    
  }
  
  //Do the actual routing.
  public function execute()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'started routing', $this->path);
    
    //Start routing.
    while($this->state < 30)
    {
      
      //Preprocessing state.
      if($this->state < 10){
        $this->preProcess();
      }
      
      //Endpoint processing state.
      elseif($this->state < 20){
        $this->endPoint();
      }
      
      //Post-processing state.
      else{
        $this->postProcess();
      }
      
    }
    
    //Enter a log entry.
    tx('Log')->message($this, 'finished routing', $this->path);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Return the file extension in the path. False if there was none.
  public function getExt()
  {
    
    $path = ($this->endpoint && substr_count($this->endpoint->base, '.') > 0)
      ? $this->endpoint->base
      : $this->path;
    
    return trim(strstr(str_replace('.part', '', $path), '.'), '.');
    
  }
  
  //Return true if a .part file was requested.
  public function isPart()
  {
    
    return substr_count($this->path, '.part') > 1;
    
  }
  
  //Returns true if the given path matches the current request path.
  public function match()
  {
    
    self::handleArguments(func_get_args(), $type, $path);
    
    //Test for type?
    if(!is_null($type))
    {
      
      //See if the type matches the current request type.
      if(!checkbit($this->type, $type)){
        return false;
      }
      
    }
    
    //Test for path? If not we are done.
    if(is_null($path)){
      return true;
    }
    
    return self::matchPath($path, $this->path) !== false;
    
  }
  
  //Return the parameters present in this route based on given route with keys.
  public function params($keys)
  {
    
    tx('Log')->message($this, 'extracting parameters', "'$keys' from '{$this->path}'");
    
    //Get the parameters.
    $params = self::matchPath($keys, $this->path);
    
    //Make sure the given path was a match.
    if($params === false){
      throw new \exception\InvalidArgument('The given path did not match the route.');
    }
    
    //Return the parameters.
    return $params;
    
  }
  
  //Reroutes to a new path starting from the current segment.
  private $reroute_cache=[];
  public function reroute($path, $prepend=false)
  {
    
    //Rerouting can only be done in the preProcessing stages.
    if($this->state >= 10){
      throw new \exception\Restriction('Can only reroute while preProcessing.');
    }
    
    //Detect cyclic reference.
    if(in_array($path, $this->reroute_cache)){
      throw new \exception\Configuration(
        'Cyclic reference occurred: The "%s" path came back to itself after %s reroutings.',
        $path, count(array_slice($this->reroute_cache, array_search($path, $this->reroute_cache)))-1
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
    $this->reroute_cache[] = implode('/', $this->history)."/$path";
    
    //Build the new path.
    $path = trim(implode('/', $this->history).'/'.implode('/', $this->future), '/');
    
    //Enter a log entry.
    tx('Log')->message($this, 'rerouting', "'{$this->path}' -> '$path'");
    
    //Set the path.
    $this->path = $path;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Walk through the given route and call the preProcessors.
  private $pre_process_cache=[];
  private function preProcess()
  {
    
    //Get the path up until now.
    $path = implode('/', $this->history);
    
    if(!in_array($path, $this->pre_process_cache))
    {
    
      //Enter a log entry.
      tx('Log')->message($this, 'preprocessing route', $path);
      
      //call the processors.
      foreach($this->getControllers($path) as $con){
        $con->callPres($this->input, $this->params($con->base));
      }
      
      //Remember
      $this->pre_process_cache[] = $path;
    
    }
    
    //Progress on to the next state.
    switch($this->state)
    {
      
      //The first segment.
      case 0:
        
        //We are at the first segment, yet we don't have a future. We will use this as empty alias.
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
        
        //If the segment is the reserved "i", and it was somehow not caught by .htaccess.
        elseif($this->future[0] == 'i'){
          $this->history[] = array_shift($this->future);
          $this->state = 5;
        }
        
        //The first segment is unreserved. We will use it as alias.
        else{
          $this->state = 3;
        }
        
        break;
        
      //Route to a component.
      case 1:
        
        //Check if we have a component name available.
        if(empty($this->future)){
          throw new \exception\NotFound('Just "/com" will lead to nowhere. Ever.');
        }
        
        //Get the component name and route there.
        $this->history[] = $component_name = array_shift($this->future);
        $this->processComponent($component_name);
        
        break;
        
      //Simple route progression.
      case 4:
        if(empty($this->future)){
          $this->state = 10;
        }else{
          $this->history[] = array_shift($this->future);
        }
        break;
      
      //Route to a system utility.
      case 2:
        throw new \exception\Programmer('Not implemented yet.');
        break;
      
      //Reroute based on an alias.
      case 3:
        $this->history[] = $alias = array_shift($this->future);
        $this->processAlias($alias);
        break;
      
      //Load a resource.
      case 5:
        #TODO: Load a resource of .htaccess failed.
        throw new \exception\Programmer('Not implemented yet.');
        break;
      
    }
     
  }
  
  //Handle the end of a route.
  private function endPoint()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'processing endpoint', $this->type.':'.$this->path);
    
    //Get the controllers.
    $controllers = $this->getControllers();
    
    //Get the controllers with an endpoint.
    foreach($controllers as $key => $con){
      if(!$con->hasEnd()){
        unset($controllers[$key]);
      }
    }
    
    //Test if we have a route matching the full path.
    if(empty($controllers)){
      throw new \exception\NotFound('This page does not exist.');
    }
    
    //Sort controllers based on the "solidness" of the paths. Parameters are weaker than statics.
    $sorted = [];
    foreach($controllers as $key => $con)
    {
      
      //Explode into segments.
      $segments = explode('/', $con->base);
      $i = 0;
      
      //Higher numbers mean that it's less solid.
      $solidness = 0;
      
      //Detect parameters. The further the parameter is away from the endpoint, the less solid the path.
      foreach($segments as $segment)
      {
        
        if($segment{0} == '$'){
          $solidness = count($segments) - $i;
        }
        
        $i++;
        
      }
      
      //Sorted.
      $sorted[$solidness][] = $con;
      
    }
    
    //The best matches.
    ksort($sorted);
    reset($sorted);
    $endpoints = current($sorted);
    
    //There can be only one.
    if(count($endpoints) > 1){
      throw new \exception\Programmer('There are conflicting endpoints for "%s".', $this->path);
    }
    
    $endpoint = $endpoints[0];
    
    //If the endpoint we're using is a component, we will know the template to use.
    if($endpoint instanceof \classes\ComponentController)
    {
      
      $this->inner_template =
        tx('Config')->paths->components.
        '/'.$endpoint->component->name.
        '/templates'.
        '/'.$endpoint->filename;
        
    }
    
    else{
      #TODO: Figure out which template to use when the controller is not a component controller.
      throw new \exception\Programmer('Not implemented yet.');
    }
    
    //Call and store the used endpoint.
    tx('Log')->message($this, 'calling endpoint', $endpoint->base);
    $endpoint->callEnd($this->input, $this->output, $this->params($endpoint->base));
    $this->endpoint = $endpoint;
    
    //Set the state to postProcessing.
    $this->state = 20;
    
  }
  
  //Walk back, backwards.
  private function postProcess()
  {
    
    //Create routes based on the history.
    $routes = [];
    foreach($this->history as $i => $segment){
      $routes[$i] = (array_key_exists($i-1, $routes) ? $routes[$i-1].'/' : '').$segment;
    }
    
    //Walk through the history of routes in reversed order and execute their postProcessors.
    foreach(array_reverse($routes) as $path){
      tx('Log')->message($this, 'postprocessing route', $path);
      foreach($this->getControllers($path) as $con){
        $con->callPosts($this->input, $this->output, $this->params($con->base));
      }
    }
    
    //Set the state to done.
    $this->state = 30;
    
  }
  
  //Route to components.
  private $process_component_cache=[];
  private function processComponent($component_name)
  {
    
    //Log.
    tx('Log')->message($this, 'processing component', $component_name);
    
    //Are we allowed to use numeric component identifiers?
    if(is_numeric($component_name) && tx('Config')->config->route_allow_numeric_components !== true){
      throw new \exception\NotFound('"%s" Is not a valid component.', $component_name);
    }
    
    //Get component info.    
    $cinfo = Component::get($component_name);
    
    //Are we using a numeric identifier? If so, we reroute.
    if(is_numeric($component_name)){
      $this->reroute($cinfo->name, true);
      $this->state = 1;
      return;
    }
    
    //Load controllers recursively.
    $loadControllers = (function($com)use(&$loadControllers){
      
      //Detect cyclic reference.
      if(in_array($com->id, $this->process_component_cache)){
        return;
      }
      
      //Add this component to the history.
      $this->process_component_cache[] = $com->id;
      
      //Load the controllers of this component.
      tx('Log')->message($this, 'loading controllers', $com->title);
      $this->addControllers($com->loadControllers($this));
      
      //Load the controllers of components extending this component.
      $com->getExtendingComponents()->each(function($com)use(&$loadControllers){
        $loadControllers($com);
      });
      
    });
    
    //Start loading.
    $loadControllers($cinfo);
    
    //Everything OK! Progress the state to simple route progression.
    tx('Log')->message($this, 'finished processing component', $component_name);
    $this->state = 4;
    
  }
  
  //Reroute based on an alias.
  private function processAlias($alias)
  {
    
    //Get the alias from the database.
    $result = tx('Sql')->exe('SELECT `value` FROM `#system_route_aliases` WHERE `key` = ?s', $alias);
    
    //Check if it wasn't found.
    if($result->count() == 0){
      throw new \exception\NotFound('"%s" Is not a valid alias.', $alias);
    }
    
    //Reroute to the alias.
    $this->reroute($result[0]->value, true);
    $this->state = 0;
    
  }
  
  //Add more controllers to our controllers.
  public function addControllers(array $controllers)
  {
    
    //Add them one by one.
    foreach($controllers as $controller){
      $this->addController($controller);
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Add a controller to our array of controllers.
  public function addController(Controller $controller)
  {
    
    //Get type and path.
    $type = $controller->type;
    $path = $controller->base;
    
    //We don't need controllers with the wrong type.
    if(!checkbit($this->type, $type)){
      throw new \exception\InvalidArgument(
        'We can not use a controller with type %s. We are type %s.', $type, $this->type
      );
    }
    
    //Add this path.
    if(!array_key_exists($path, $this->controllers)){
      $this->controllers[$path] = [];
    }
    
    //Add the controller.
    $this->controllers[$path][] = $controller;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Get the controllers stored under the given path.
  public function getControllers($path=null)
  {
    
    //Prepare variables.
    $path = (is_null($path) ? $this->path : $path);
    $result = [];
    
    //Iterate our controllers to filter them down.
    foreach($this->controllers as $base => $controllers)
    {
      
      //Needs to be an exact match.
      if(substr_count($base, '/') !== substr_count($path, '/')){
        continue;
      }
      
      //If the path doesn't match.
      if(self::matchPath($base, $path)===false){
        continue;
      }
      
      //Filters passed. Add to results.
      $result = array_merge($result, $controllers);
      
    }
    
    //Output.
    return $result;
    
  }
  
}
