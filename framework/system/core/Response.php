<?php namespace core;

class Response
{
  
  //Public properties.
  public
    $redirect_url=false;
  
  //When this class initiates, we instruct the router to route to the page request path.
  public function init()
  {
    
    //Enter a log entry.
    tx('Log')->message($this, 'Response class initialized.');
    
  }
  
  //Output.
  public function output()
  {
    
    //Get the path.
    $path = tx('Request')->url->segments->path;
    
    //Bite off the system base (the system base length + 2 for the leading and trailing slashes).
    $path = $start = substr($path, strlen(tx('Config')->urls->path)+2);
    
    //Clean the path.
    $path = \classes\Router::cleanPath($path);
    
    //Redirect if the router object changed the path.
    if($path != $start){
      tx('Log')->message($this, 'clean path redirect', "'$start' -> '$path'");
      return $this->redirect(url("/$path"))->_handleRedirect();
    }
    
    //Store the mime so we can pass a reference.
    $mime = tx('Request')->accept['mimes'][0]['value'];
    
    //Output.
    $this->outputRoute(tx('Request')->method(), $path, tx('Request')->data->copy(), $mime);
    
  }
  
  //Uses the given Router object to forge a response for our client.
  public function outputRoute($method, $path, \classes\DataBranch $data, $mime, $part=null, $to_stream=true)
  {
    
    //Make the Materials.
    $materials = new \classes\Materials($data);
    
    //Try to execute the router.
    try
    {
      
      //Make the router.
      $router = new \classes\Router($method, $path, $materials);
      
      //Execute the router.
      $router->execute();
      
      //Get required information from the router.
      $part = (is_bool($part) ? $part : $router->isPart());
      
      //Use the mime-type that belongs to the router extension if the endpoint had one.
      if($router->getExt()){
        $mime = tx('Mime')->getMime($router->getExt());
      }
      
    }

    //An exception was caught. We will use this to create our page.
    catch(\exception\Exception $e)
    {
      
      #TODO: New Standard data: Exception.
      $materials->output = $e;
      $materials->inner_template = false;
      $materials->outer_template = tx('Resource')->template('error');
      
      $part = (is_bool($part) ? $part : false);
      
      trace($materials);
      echo "<table>{$e->xdebug_message}</table>";
      exit;
      
    }
    
    //The mime is now certain.
    $materials->mime = $mime;
    
    //Get the inner template.
    $inner_template = (is_object($materials->inner_template)
      ? $this->getTemplate($materials->inner_template, $mime)
      : false
    );
    
    //Get the outer template.
    $outer_template = (is_object($materials->outer_template) && !$part
      ? $this->getTemplate($materials->outer_template, $mime)
      : false
    );
    
    //Convert the output using a templator?
    if($inner_template)
    {
      
      //Create the templator.
      $templator = $materials->output->createTemplator($materials);
      
      //Generate the template.
      $output_data = (new \classes\Render($templator, $inner_template, [
        'errors' => Data($materials->errors),
        'warnings' => Data($materials->warnings)
      ]))->generate();
      
    }
    
    //Convert the output using standard converters.
    else
    {
      
      //Get the converter.
      $converter = $materials->output->getConverterByMime($mime);
      
      //Output directly to stream?
      if(!($outer_template) && $to_stream){
        $converter->output();
        return;
      }
      
      //Output to this variable.
      $output_data = $converter->output(false);
      
    }
    
    //Wrap output data in an outer template?
    if($outer_template)
    {
      
      //Create the outer-template data.
      $data = tx('Outputting')->standardize($output_data);
      
      //Create the templator.
      $templator = $data->createTemplator($materials);
      
      //Render.
      $output_data = (new \classes\Render(
        $templator,
        $outer_template,
        $materials->outer_template_data
      ))->generate();
      
    }
    
    //Output to stream?
    if($to_stream){
      $output_data->setHeader('Content-type', $mime.'; charset=utf-8');
      $output_data->output();
      return;
    }
    
    //Return the output data.
    return $output_data;
    
  }
  
  //Set the URL to redirect to.
  public function redirect(\classes\Url $url)
  {
    
    //Set the redirect URL.
    $this->redirect_url = $url;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Returns true if we redirected, or false otherwise.
  public function redirected()
  {
    
    return ($this->redirect_url !== false);
    
  }
  
  //Return the right template file in given directory based on mime-type.
  public function getTemplate(\classes\locators\Template $locator, $mime)
  {
    
    //Get the directory.
    $directory = $locator->locate();
    
    //Get all possible templates.
    $templates = files("$directory/{".implode(',', tx('Mime')->getTypes($mime)).'}.php', GLOB_BRACE);
    
    //This template does not exist.
    if(count($templates) == 0){
      return false;
    }
    
    //Huh?
    if(count($templates) > 1){
      throw new \exception\Programmer('There are multiple "%s"-templates in "%s".', $mime, $directory);
    }

    return $templates[0];
    
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
