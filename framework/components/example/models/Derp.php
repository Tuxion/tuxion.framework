<?php namespace components\models\example;

class Derp extends \classes\sql\BaseModel
{
  
  static
    $table_name = '#com__example_derp',
    $relations = [
      'Test' => ['test_id', 'Test.id', 'LEFT']
    ];
    
}
