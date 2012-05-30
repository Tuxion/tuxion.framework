<?php namespace classes;

class Render
{
  
  //Private properties.
  private
    $template=false,
    $mime=null;
  
  //Returns the website title.
  public function getTitle()
  {
    
    return tx('Config')->config->title;
    
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
    
    //Get all possible templates.
    $templates = files("$template.(".implode('|', tx('Mime')->getTypes()).')');
    
    trace($templates);
    exit;
    
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

}
