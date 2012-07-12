<?php namespace classes;

class Render
{
  
  //Private properties.
  private
    $template=false,
    $mime=null,
    $data=null,
    $output='NotGenerated';
  
  //Returns the website title.
  public function getTitle()
  {
    
    return tx('Config')->config->title;
    
  }
  
  //Do a request to a different route from within the template.
  public function request($path, DataBranch $data = null)
  {
    
    $router = new Router(tx('Request')->method(), $path, is_null($data) ? Data([]) : $data);
    $ext = $router->getExt();
    
    //If we have an explicit extension, we will try to return the data in that format.
    if($ext){
      $mime = tx('Mime')->getMime($ext);
    }
    
    //Otherwise we will use the mime-type that we are already using for this template.
    else{
      $mime = $this->mime;
    }

    return ((new self)
      ->setMime($mime)
      ->setTemplate($router->inner_template)
      ->setData($router->output)
      ->generate()
      ->getOutput()
    );
    
  }
  
  //Set the data that will be injected into the template.
  public function setData(DataBranch $data)
  {
    
    $this->data = $data;
    return $this;
    
  }
  
  //Set the template that we will inject the data into.
  public function setTemplate($template=null)
  {
    
    //No template?
    if(!is_string($template)){
      $this->template = false;
      return $this;
    }
    
    //We need to know the mime-type before.
    if(is_null($this->mime)){
      $this->mime = 'text/html';
    }
    
    //Get all possible templates.
    $templates = files("$template/{".implode(',', tx('Mime')->getTypes($this->mime)).'}.php', GLOB_BRACE);
    
    //This template does not exist.
    if(count($templates) == 0){
      $this->template = false;
      return $this;
    }
    
    //Huh?
    if(count($templates) > 1){
      throw new \exception\Programmer('There are multiple "%s"-templates in "%s".', $this->mime, $template);
    }
    
    //Set the template.
    $this->template = $templates[0];
    
    //Enable chaining.
    return $this;
    
  }
  
  //Generate the output.
  public function generate()
  {

    //Try to generate the output.
    try{
      
      //Use a default mime-type?
      if(is_null($this->mime)){
        $this->mime = 'text/html';
      }
      
      //Use default data?
      if(is_null($this->data)){
        $this->data = new DataBranch([]);
      }
      
      //We need a template.
      if($this->template===false){
        #TODO: proper fallback templating
        ob_start();
        trace($this->data->toArray());
        $this->output = ob_get_contents();
        ob_end_clean();
      }
      
      else{
        ob_start();
        extract($this->data->get());
        require($this->template);
        $this->output = ob_get_contents();
        ob_end_clean();
      }
      
    }
    
    //If we fail, generate an error.
    catch(\exception\Exception $e){
      return $this->generateError($e);
    }
    
    return $this;
    
  }
  
  //Find the right template based on the mime-type and template name.
  private function findTemplate()
  {
    
    # code...
    
  }
  
  //Generate output based on an exception.
  public function generateError($e)
  {
    
    $this->output = $e->getMessage();
    $this->output .= "<br />";
    $this->output .= tx('Debug')->printTrace($e->getTrace());
    
    return $this;
    
  }
  
  //Set the mime type that will be used for rendering.
  public function setMime($mime)
  {
    
    //Expecting $mime to be string.
    if(!(is_null($mime) || is_string($mime))){
      throw new \exception\InvalidArgument(
        'Expecting $mime to be string or null. %s given.', ucfirst(typeof($mime))
      );
    }
    
    $this->mime = $mime;
    
    return $this;
    
  }
  
  //Return the output.
  public function getOutput()
  {
    
    return $this->output;
    
  }

}
