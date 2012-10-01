<?php namespace components\models\example;

class Test extends \classes\SqlBaseModel
{
  
  static
    $table_name = '#com__example_test';
    
  //Returns the title and description the prettiest way possible.
  public function get_post()
  {
    
    $this->cache();
    
    return '<article><h1>'.$this->arrayGet('title').'</h1><p>'.
    $this->arrayGet('description').'</p></article>';
    
  }
  
}
