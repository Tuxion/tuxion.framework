<?php namespace components\models\example;

class Test extends \classes\sql\BaseModel
{
  
  static
    $table_name = '#com__example_test',
    $relations = [
      'Derp' => ['id', 'Derp.test_id', 'LEFT']
    ];

  //Returns the title and description the prettiest way possible.
  public function get_post()
  {
    
    //Cache the result because this is a heavy operation.
    $this->cache();
    
    return '<article><h1>'.$this->arrayGet('title').'</h1><p>'.
    $this->arrayGet('description').'</p></article>';
    
  }
  
}
