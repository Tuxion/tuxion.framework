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
    $exception_occurred_in=null,
    $mime;
  
  //Set the input.
  public function __construct(DataBranch $input)
  {
    
    $this->input = $input;
    
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
  
}
