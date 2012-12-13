<?php namespace classes;

class Materials
{
  
  //Public properties.
  public
    $router=null,
    $input,
    $warnings=[],
    $errors=[],
    $inner_template=null,
    $outer_template=null,
    $outer_template_data=[],
    $output=null,
    $full_path,
    $exception,
    $exception_occurred_in=null,
    $mime,
    $code = 200;
  
  //Set the input.
  public function __construct(\outputting\nodes\Standard $input)
  {
    
    $this->input = $input;
    
  }
  
  //Use an exception to fill this Materials object, effectively causing it to create an error page.
  public function exception(\Exception $e)
  {
    
    //Get the exception status code.
    $code = tx('Debug')->getExceptionResponseCode($e);
    
    //Override inner template and data.
    $this->output = tx('Outputting')->standardize($e);
    $this->inner_template = tx('Resource')->template(
      'template', tx('Config')->paths->outputting.'/error'
    );
    
    //Override outer template and data.
    $this->outer_template = tx('Resource')->template('error');
    $this->outer_template_data = [];
    $this->addTemplateData([
      'type' => wrap($e)->baseclass()->get(),
      'code' => $code
    ]);
    
    //Store the exception and the exception code.
    $this->exception = $e;
    $this->code = $code;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Adds data for the outer template.
  public function addTemplateData(array $data)
  {
    
    $this->outer_template_data = array_merge($this->outer_template_data, $data);
    
  }
  
  //Adds errors.
  public function addErrors(array $errors)
  {
    
    $this->errors = array_merge($this->errors, $errors);
    
  }
  
  //Adds warnings.
  public function addWarnings(array $warnings)
  {
    
    $this->warnings = array_merge($this->warnings, $warnings);
    
  }
  
  //Return true if this contains an exception.
  public function isException()
  {
    
    return ($this->exception instanceof \Exception);
    
  }
  
  //Return the status code with it's message.
  public function getStatus()
  {
    
    return $this->code.' '.($this->isException() ?
      $this->exception->getMessage()
      : tx('Stati')->getMessage($this->code)
    );
    
  }
  
}
