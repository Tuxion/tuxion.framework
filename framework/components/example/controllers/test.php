<?php namespace components\controllers\example;

route(GET, 'test')->pre('Settings the template', function(){
  $this->setTemplate('minimal');
});

//Get a single Test from the database.
route(GET, 'test/$int')->end('Loading a Test from the database.', function($id){
  $this->output($this->fetchA('Test', $T)->where($T->id, $id)->execute());
});
