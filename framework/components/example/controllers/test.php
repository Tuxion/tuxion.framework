<?php namespace components\controllers\example;

//Get a single Test from the database.
route(GET, 'test/$int')->end('Loading a Test from the database.', function($id){
  $this->output($this->fetchA('Test', $T)->where($T->id, $id)->execute());
});
