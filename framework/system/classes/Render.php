<?php namespace classes;

class Render
{
  
  //Private properties.
  private
    $templator,
    $template,
    $data;
  
  //Set the templator.
  public function __construct(BaseTemplator $templator, $template, array $data = [])
  {
    
    $this->templator = $templator;
    $this->setTemplate($template);
    $this->data = $data;
    
  }
  
  //Set the template that we will inject the data into.
  public function setTemplate($template)
  {
    
    //No template?
    if(!is_string($template)){
      throw new \exception\InvalidArgument('Expecting $template to be string. %s given.', typeof($template));
    }
    
    //Does it exist?
    if(!file_exists($template)){
      throw new \exception\ResourceMissing('Given template (%s) must be an existing file.', $template);
    }
    
    //Set the template.
    $this->template = $template;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Generate the output.
  public function generate()
  {
    
    //Store references to the required variables under obscure names.
    $___data =& $this->data;
    $___path =& $this->template;
    
    //Create the templator function that we will bind to the templator.
    $templator = function()use(&$___data, &$___path){
      $t = $templator = $this;
      extract($___data);
      unset($___data);
      ob_start();
        require($___path);
        $r = new OutputData(ob_get_contents(), $t->getHeaders());
      ob_end_clean();
      return $r;
    };
    
    //Bind it.
    $templator = $templator->bindTo($this->templator);
    
    //Call it.
    return $templator();
    
  }

}
