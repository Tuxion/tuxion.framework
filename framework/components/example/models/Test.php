<?php namespace components\models\example;

use \classes\sql\BaseModel;

class Test extends BaseModel
{
  
  static
    $table_name = '#com__example_test',
    $relations = [
      'Derp' => ['id', 'Derp.test_id', 'LEFT']
    ];
  
  //Return the ID.
  public function get_id()
  {
    
    return '#'.$this->_get('id');
    
  }
  
}
