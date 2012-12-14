<?php namespace classes\route;

use \classes\Materials;
use \classes\Component;
use \classes\locators\Component as ComponentLocator;

abstract class BaseProcessor
{
  
  //Private properties.
  private
    $description,
    $callback,
    $context;
  
  //Protected properties.
  protected
    $materials=null;
  
  //Store the given callback.
  public function __construct($description, \Closure $callback, ControllerContext $context)
  {
    
    //Validate argument.
    if(!is_string($description)){
      throw new \exception\InvalidArgument(
        'Expecting $description to be string. %s given.',
        ucfirst(typeof($description))
      );
    }
    
    //Set properties.
    $this->description = $description;
    $this->callback = $callback->bindTo($this);
    $this->context = $context;
    
  }
  
  //Call the associated callback with the arguments in given array.
  public function execute(Materials $materials, array $arguments)
  {
    
    //Define execution tracker.
    static $executing=false;
    
    //Detect nested execution. That would be bad!
    if($executing){
      throw new \exception\Restriction(
        'Nested execution occurred; %stried %s while %s.',
        ($this->description == $executing ? 'yo dawg, you ' : ''),
        strtolower(trim($this->description, ' .!?')),
        strtolower(trim($executing, ' .!?'))
      );
    }
    
    //Set the materials so that helper functions can use it.
    $this->materials = $materials;
    
    //We are now executing the following:
    $executing = $this->description;
    
    //Execute the callback.
    try{
      $cb = $this->callback;
      call_user_func_array($cb, $arguments);
    }
    
    //Add some extra data when an error occurs.
    catch(\exception\Exception $e){
      $materials->exception_occurred_in = $this;
      $this->materials = null;
      $executing = false;
      throw $e;
    }
    
    //No longer executing.
    $executing = false;
    
    //We should not have materials set when not executing.
    $this->materials = null;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Return the context.
  public function getContext()
  {
    
    return $this->context;
    
  }
  
  
  ##
  ## PROCESSING HELPERS
  ##
  
  //Give some programmer feedback to prevent confusion when working with pre-, post- and end-processors.
  public function __call($key, $args)
  {
    
    $allowed = [];
    
    foreach(['PreProcessor', 'PostProcessor', 'EndPoint'] as $class)
    {
      
      if(method_exists(__NAMESPACE__.'\\'.$class, $key)){
        $allowed[] = $class;
      }
      
    }
    
    if(empty($allowed)){
      throw new \exception\NotImplemented('There is no processor method named "%s".', $key);
    }
    
    throw new \exception\Restriction(
      'The %s method can only be used in %s.',
      $key, implode(' and ', $allowed)
    );
    
  }
  
  //Validate using the Validator class.
  public function validate($data, $rules)
  {
    
    //Extract raw arguments.
    raw($data, $rules);
    
    //Return a new Validator.
    return new Validator($data, $rules);
    
  }
  
  //Set the outer template and optionally provide some data.
  public function setTemplate($identifier, $data=null)
  {
    //We need a materials.
    $this->needsMaterials('to set a template');
    
    //Extract the raw identifier.
    raw($identifier);
    
    //Set the template.
    $this->materials->outer_template = tx('Resource')->template($identifier);
    
    //Set the data if given.
    if(!is_null($data)){
      $this->addTemplateData($data);
    }
    
  }
  
  //Adds data to the template.
  public function addTemplateData($data)
  {
    
    //We need materials.
    $this->needsMaterials('to add data to the template');
    
    //Extract raw data.
    raw($data);
    
    //Delegate to the materials interface.
    $this->materials->addTemplateData($data);
    
  }
  
  //Returns the input.
  public function input()
  {
    
    $this->needsMaterials('to get input');
    
    return $this->materials->input->raw();
    
  }
  
  //Return the component of the given name or id.
  public function component($identifier = null)
  {
    
    //Return the component based on given identifier.
    if(!is_null($identifier)){
      return Component::get($identifier);
    }
    
    //Get the locator from our context.
    $locator = $this->context->getLocator();
    
    //If the locator is no ComponentLocator, we can't do anything.
    if(!($locator instanceof ComponentLocator)){
      throw new \exception\Restriction('Can only leave $identifier empty when you are in component context.');
    }
    
    //Return the Component object by retrieving it using the locator.
    return Component::get($locator);
    
  }
  
  //Checks if we have a materials and uses the given string to generate an error if we do not.
  protected function needsMaterials($for_what)
  {
    
    //That's OK then.
    if(!is_null($this->materials)){
      return true;
    }
    
    //Generate an error.
    throw new \exception\Restriction('The processor needs materials %s.', strtolower($for_what));
    
  }
  
  
}
