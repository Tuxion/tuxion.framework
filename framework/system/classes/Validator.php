<?php namespace classes;

class Validator
{
  
  //Implement traits.
  use \traits\Successable;
  
  //Private properties.
  private
    $data=null,
    $rules=[],
    $errors=[];
  
  //Constructor accepts the provided data and can fire away with an array of rules right away.
  public function __construct($data, $rules=[])
  {
  
    $this->data = data_of($data);
    $this->validate($rules);
    
  }
  
  //Validate data with a single rule.
  public function __call($rule, $options)
  {
    
    return $this->validate([$rule=>$options]);
    
  }
  
  //Validate data based on multiple rules.
  public function validate($rules=null)
  {
    
    //Use the given rules or the rules set earlier.
    $this->rules = is_array($rules) ? $rules : $this->rules;
    
    //Let's be optimistic.
    $valid = true;
    
    //Iterate the rules.
    foreach($this->rules as $key=>$val)
    {
      
      //If the key is manually set, that will be the rule and the $val will be the options.
      if(is_string($key)){
        $rule = $key;
        $options = is_array($val) ? $val : [$val];
      }
      
      //If it's a numeric key, we can assume the rule has no options.
      else{
        $rule = $val;
        $options = [];
      }
      
      //Does this rule even exist?!
      if(!method_exists($this, "_$rule")){
        throw new \exception\InvalidArgument('%s is not a valid rule.', ucfirst($rule));
      }
      
      //Find out what the validation-method has to say about this.
      $return = call_user_func_array([$this, "_$rule"], $options);
      
      //Validate.
      $valid = (!$valid 
        ? $valid
        : ((is_null($this->check_rule('required')) && is_null($this->data)) || $return===true)
      );
      
      //If the validation-method returned an error, store it for later.
      if($return !== true){
        $this->errors[] = strtolower(trim($return, '.!? '));
      }
        
    }
    
    $this->success = $valid;
    
  }
  
  //Returns true if a the given rule was set and the data passes it.
  //Returns false if the given rule was set and the data did not pass.
  //Returns null if the given rule was not set.
  public function check_rule($certain_rule)
  {
    
    foreach($this->rules as $key => $val)
    {
      
      if(is_string($key)){
        $rule = $key;
        $options = is_array($val) ? $val : [$val];
      }
      
      else{
        $rule = $val;
        $options = [];
      }
      
      if($rule === $certain_rule){
        if(method_exists($this, "_$rule")){
          return call_user_func_array([$this, "_$rule"], $options) === true;
        }else{
          throw new \exception\InvalidArgument('%s is not a valid rule.', ucfirst($rule));
        }
      }
      
    }
    
    return null;
    
  }
  
  //Returns the array of encountered errors.
  public function errors()
  {
    return $this->errors;
  }
  
  //Retrieves the data after it has been type juggled with.
  public function get_data()
  {
    
    return $this->data;
    
  }
  
  //Returns true for arrays with a size between $min and $max, for strings with a length between $min and.
  // $max, for numbers between $min and $max
  private function _between($min=0, $max=-1)
  {
    
    if(!(is_numeric($min) && is_numeric($max))){
      throw new \exception\InvalidArgument('Expecting numbers for both $min and $max.');
    }
    
    if($max <= $min){
      throw new \exception\InvalidArgument('$min comes first, then $max.');
    }
    
    if($this->check_rule('string')===true){
      if(!(strlen($this->data) >= $min && ($max < 0 ? true : strlen($this->data) <= $max))){
        return "The value must be between $min and $max characters.";
      }
      return true;
    }
    
    elseif($this->check_rule('number')===true){
      if(!($this->data >= $min && ($max < 0 ? true : $this->data <= $max))){
        return "The value must be between $min and $max.";
      }
      return true;
    }
    
    else{
      switch(gettype($this->data))
      {
        
        case 'array':
          if(!(count($this->data) >= $min && ($max < 0 ? true : count($this->data) <= $max))){
            return "The value must contain between $min and $max nodes.";
          }
          return true;
        
        case 'string':
          if(!(strlen($this->data) >= $min && ($max < 0 ? true : strlen($this->data) <= $max))){
            return "The value must be between $min and $max characters.";
          }
          return true;
          
        case 'integer':
        case 'float':
        case 'number':
        case 'double':
          if(!($this->data >= $min && ($max < 0 ? true : $this->data <= $max))){
            return "The value must be between $min and $max.";
          }
          return true;
          
        default:
          return 'The value is of a format which can not lie between 2 numbers: '.gettype($this->data);
      
      }
    }
    
  }
  
  //Fails validation if this is not set.
  private function _required()
  {
    
    if($this->data !== null){
      return true;
    }
    
    return "It is a required field.";
    
  }
  
  //Fails validation if node is empty.
  private function _not_empty()
  {
    
    if(empty($this->data)){
      return "The value can not be empty.";
    }
    
    return true;
    
  }
  
  //Checks email address format.
  private function _email()
  {
    
    if(!filter_var($this->data, FILTER_VALIDATE_EMAIL)){
      return "The value must be a valid email address.";
    }

    return true;
    
  }
  
  //Checks if content of this node is numeric.
  private function _number($type='int')
  {
    
    switch($type)
    {
      
      case 'int':
      case 'integer':
        $converted = (integer) $this->data;
        break;
      
      case 'float':
        $converted = (float) $this->data;
        break;
        
      case 'double':
        $converted = (double) $this->data;
        break;
        
      default:
        throw new \exception\InvalidArgument('Invalid data type.');
      
    }
    
    if((is_string($this->data)) ? ((string) $converted === $this->data) : ($converted === $this->data)){
      $this->data = $converted;
      return true;
    }
    
    return "The value must be a number.";
  
  }
  
  //Validate if the value could be used as a string.
  private function _string()
  {
    
    //Check if the value is a string, if not.. maybe it's something we can cast to a string.
    if(!is_string($this->data))
    {
      
      if(is_array($this->data) || ((is_object($this->data) && !method_exists($this->data, '__toString')))){
        return 'The value must be textual.';
      }
      
      $this->data = (string) $this->data;
      
    }
    
    return true;
    
  }
  
  //Greater than.
  private function _gt($number)
  {
    
    if($this->check_rule('number') !== false && $this->data > $number){
      return true;
    }
    
    return "The value must be greater than $number.";
    
  }
  
  //Lesser than.
  private function _lt($number)
  {
    
    if($this->check_rule('number') !== false && $this->data < $number){
      return true;
    }
    
    return "The value must be lesser than $number.";
    
  }
  
  //Equal to.
  private function _eq($value)
  {
    
    if($this->check_rule('number') !== false && $this->data == $value){
      return true;
    }
    
    return "The value must be equal to $value";
    
  }
  
  //Fails validation if the given input can not be used as JavaScript variable name.
  private function _javascript_variable_name()
  {
  
    if($this->check_rule('string') === true && preg_match('~^[a-zA-Z_$][0-9a-zA-Z_$]*$~', $this->data) == 1){
      return true;
    }
    
    return 'The value must be a javascript variable name.';
  
  }
  
  //Fails validation if value can not easily be used as boolean.
  private function _boolean()
  {
  
    if(is_bool($this->data)){
      return true;
    }
    
    if(in_array(@intval($this->data), [0, 1])){
      $this->data = (bool) intval($this->data);
      return true;
    }
    
    return 'Value must be a boolean.';
    
  }
  
  //Fails validation if value is not an array.
  private function _array()
  {
    
    if(is_array($data)){
      return true;
    }
    
    return 'Value must be an array.';
    
  }
  
  //Fails validation if our URL parser can not interpret this as a URL.
  private function _url()
  {
    
    if($this->_string() !== true){
      return 'Value must be a valid URL.';
    }
  
    try{
      $segments = Url::parse($this->data);
    }
    
    catch(\exception\Unexpected $e){
      return 'Value must be a valid URL.';
    }
    
    //The arguments passed to this function represent segments that are required.
    foreach(func_get_args() as $segment){
      if(!array_key_exists($segment, $segments)){
        return 'Value must be a valid URL.'; 
      }
    }
    
    return true;
    
  }
  
  private function _password()
  {
    
    //Validate a password is strong enough.
    if(tx('Security')->get_password_strength($this->data) < SECURITY_PASSWORD_STRENGTH)
      return 'The value must be a strong password please mix at least '.SECURITY_PASSWORD_STRENGTH.
        ' of the following: uppercase letters, lowercase letters, numbers and special characters].';
    
    return true;
    
  }
  
}
