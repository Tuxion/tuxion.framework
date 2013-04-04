<?php namespace classes\route;

use \classes\Materials;
use \classes\Component;
use \classes\data\PathWrapper;
use \classes\data\FileWrapper;

class Router
{
  
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
  
  //Private properties.
  private
    $state=0,
    $history=[],
    $fututre=[],
    $controllers=[];
  
  //Public properties.
  public
    $type,
    $path,
    $materials;
  
  //The constructor will set some properties.
  public function __construct($type, $path, Materials $materials)
  {
    
    //Set the request method.
    $this->type = $type;
    
    //Clean the path.
    $this->path = path($path)->clean()->get();
    
    //Split the path into segments, and put them in the future.
    $this->future = explode('/', $this->path);
    
    //Add ourselves to the materials.
    $materials->router = $this;
    
    //Set the materials.
    $this->materials = $materials;
    
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
  
  public function getExt()
  {
    
    $path = ($this->materials->full_path && substr_count($this->materials->full_path, '.') > 0)
      ? $this->materials->full_path
      : $this->path;
    
    return path($path)->clean()->getFile()->getExt();
    
  }
  
  //Return true if a .part file was requested.
  public function isPart()
  {
    
    return path($this->path)->getFile()->isPart();
    
  }
  
  //Returns true if the given path matches the current request path.
  public function match()
  {
    
    self::handleArguments(func_get_args(), $type, $path);
    
    //Test for type?
    if(!is_null($type))
    {
      
      //See if the type matches the current request type.
      if(wrap($type)->hasBit($this->type)){
        return false;
      }
      
    }
    
    //Test for path? If not we are done.
    if(is_null($path)){
      return true;
    }
    
    return path($this->path)->isMatch(path($path));
    
  }
  
  //Return the parameters present in this route based on given route with keys.
  public function params($keys)
  {
    
    //Create a log entry.
    tx('Log')->message($this, 'extracting parameters', "'$keys' from '{$this->path}'");
    
    //Get keys and values wrapped.
    $keys = path($keys);
    $values = path($this->path);
    
    //Make sure the given path is a match.
    if(!$values->isMatch($keys)){
      throw new \exception\InvalidArgument('The given path does not even match the route.');
    }
    
    //Return the parameters.
    return $values->getValues($keys);
    
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
      throw new \exception\InternalServerError(
        'Cyclic reference occurred: The "%s" path came back to itself after %s reroutings.',
        $path, count(array_slice($this->reroute_cache, array_search($path, $this->reroute_cache)))-1
      );
    }
    
    //Prepare some variables.
    $path = path($path)->clean()->get();
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
      
      //For every controller corresponding to our request.
      foreach(tx('Controllers')->getAll($this->type, $path) as $con){
        $con->callPres($this->materials, $this->params($con->path));
      }
      
      //Remember.
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
        throw new \exception\Todo('System utilities are not available as of yet.');
        break;
      
      //Reroute based on an alias.
      case 3:
        $this->history[] = $alias = array_shift($this->future);
        $this->processAlias($alias);
        break;
      
      //Load a resource.
      case 5:
        #TODO: Load a resource if .htaccess failed.
        throw new \exception\Todo('Fall-back server resource loading is not available as of yet.');
        break;
      
    }
     
  }
  
  //Handle the end of a route.
  private function endPoint()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'processing endpoint', $this->type.':'.$this->path);
    
    //Get the controllers.
    $controllers = tx('Controllers')->getAll($this->type, $this->path);
    
    //Get the controllers with an endpoint.
    foreach($controllers as $key => $con){
      if(!$con->hasEnd()){
        unset($controllers[$key]);
      }
    }
    
    //Test if we have a route matching the full path.
    if(empty($controllers)){
      throw new \exception\NotFound(
        'This page does not exist: "%s/%s"', tx('Request')->url->format('%scheme://%domain'), $this->path
      );
    }
    
    //Sort controllers based on the "solidness" of the paths. Parameters are weaker than statics.
    $sorted = [];
    foreach($controllers as $key => $con)
    {
      
      //Explode into segments.
      $segments = explode('/', $con->path);
      $i = 0;
      
      //Higher numbers mean that it's less solid.
      $solidness = 0;
      
      //Save the total number of segments, so we won't have to count them at every iteration of them.
      $total = count($segments);
      
      //Detect parameters. The further the parameter is away from the endpoint, the less solid the path.
      foreach($segments as $segment)
      {
        
        if($segment{0} == '$'){
          $solidness = $total - $i;
        }
        
        $i++;
        
      }
      
      //Sorted.
      $sorted[$solidness][] = $con;
      
    }
    
    //The best matches.
    ksort($sorted);
    reset($sorted);
    $controllers = current($sorted);
    
    //There can be only one.
    if(count($controllers) > 1){
      throw new \exception\InternalServerError(
        'There are conflicting controllers with endpoints for "%s".', $this->path
      );
    }
    
    //Get the controller and its endpoint.
    $controller = $controllers[0];
    $endpoint = $controller->getEnd();
    
    //Create the locator to the inner template.
    $this->materials->inner_template = (
      $endpoint
      ->getLocator()
      ->template($endpoint->getFile()->getName())
    );
    
    //Call the endpoint.
    tx('Log')->message($this, 'calling endpoint', $controller->path);
    $endpoint->execute($this->materials, $this->params($controller->path));
    
    //Store the full path in the materials.
    $this->materials->full_path = $controller->path;
    
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
    foreach(array_reverse($routes) as $path)
    {
      
      //Create a log entry.
      tx('Log')->message($this, 'postprocessing route', $path);
      
      //Iterate the controllers corresponding to this route.
      foreach(tx('Controllers')->getAll($this->type, $path) as $con){
        $con->callPosts($this->materials, $this->params($con->path));
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
      $com->loadControllers($this);
      
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
      throw new \exception\NotFound('"%s" Is not an existing alias.', $alias);
    }
    
    //Reroute to the alias.
    $this->reroute($result[0]->value, true);
    $this->state = 0;
    
  }
  
}
