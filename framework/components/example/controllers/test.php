<?php namespace components\controllers\example;

//Must have the right permissions.
route(GET|POST|PUT|DELETE, 'test')->pre('Validating permissions.', function(){
  $this->permissions('eat_pie');
});

//Get Tests from the database.
route(GET, 'test/all')->end('Loading Tests from database.', function(){
  $this->output($this->fetchAll('Test')->execute());
});
