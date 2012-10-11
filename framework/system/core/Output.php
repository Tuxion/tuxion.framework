<?php namespace core;

class Output
{
  
  //Public properties.
  public
    $redirect_url=false;
    
  //When this class initiates, we instruct the router to route to the page request path.
  public function init()
  {
    //Enter a log entry.
    tx('Log')->message($this, 'Output class initializing.');
    
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
    
    //Get the requested mime types.
    $mime = tx('Request')->accept['mimes'];
    
    //Make the router.
    $router = new \classes\Router(tx('Request')->method(), $path, tx('Request')->data->copy());
    $router->execute();

    //Output! :O
    echo $this->route($router, $mime);
    
    //Enter a log entry.
    tx('Log')->message($this, 'Output class initialized.');
    
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
  
  //Uses the given Router object to forge a response for our client.
  public function route(\classes\Router $router, $output_mimes)
  {
    
    //Get the output data.
    $data = $router->output;
    
    //Normalize output mimes argument.
    if(is_string($output_mimes)){
      $output_mimes = [$output_mimes];
    }
    
    //Get the requested file extension.
    $ext = $router->getExt();
    
    //If there is one, that will be most important.
    if($ext){
      $mime = tx('Mime')->getMime($ext);
    }
    
    //Otherwise we will carefully select the best mime to use from the options that were given.
    else{
      $mime = $output_mimes[0]['value'];
    }
    
    //Render the inner template.
    $inner = (new \classes\Render)
      ->setData($router->output)
      ->setMime($mime)
      ->setTemplate($router->inner_template)
      ->generate();
    
    //Render the outer template.
    $outer = (new \classes\Render)
      ->setData(Data(['content' => $inner->getOutput()]))
      ->setMime($mime)
      ->setTemplate($router->outer_template)
      ->generate();
    
    //Set the headers.
    header('Content-type: '.$mime.'; charset=utf-8');
    
    echo $outer->getOutput();
    
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
