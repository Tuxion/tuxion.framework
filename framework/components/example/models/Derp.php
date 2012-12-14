<?php namespace components\models\example;

use \classes\sql\BaseModel;

class Derp extends BaseModel;
{
  
  static
    $table_name = '#com__example_derp',
    $relations = [
      'Test' => ['test_id', 'Test.id', 'LEFT']
    ];
    
}
