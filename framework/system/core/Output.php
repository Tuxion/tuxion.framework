<?php namespace core;

class Output
{
  
  //Public properties.
  public
    $redirect_url=false;
    
  //When this class initiates, we instruct the router to route to the page request path.
  public function init()
  {
    
    //Get the path.
    $path = tx('Request')->url->segments->path;
    
    //Bite off the system base (the system base length + 2 for the leading and trailing slashes).
    $path = $start = substr($path, strlen(tx('Config')->urls->path)+2);
    
    //Clean the path.
    $path = \classes\Router::cleanPath($path);
    
    //Redirect if the router object changed the path.
    if($path !== $start){
      return $this->redirect(url("/$path"))->_handleRedirect();
    }
    
    //Output! :O
    $this->output(new \classes\Output(tx('Request')->method(), $path, tx('Request')->data(), $output_type));
    
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
    
    return ($this->redirect_url instanceof \classes\Url);
    
  }
  
  //Uses the given Output object to forge a request for our client.
  public function output(\classes\Output $output)
  {
    
    # code...
    
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
