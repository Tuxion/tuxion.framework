[<? echo $t->get()->map(function($row){
  return "{\"id\": \"{$row->id}\"}";
})->join(', '); ?>]
