<?php namespace components\controllers\example;


c()->template('minimal');

c(GET, 'test')->end('Testing something.', function(){
  $this->output = [
    'foo' => 'bar',
    'nyerk' => 'snarl'
  ];
});

c(GET, 'test/foobar')->end('Asd.', function(){
  echo 'asd';
});


// c(GET, 'items') // all items -> itemList hasMany(itemSmall)
// c(GET, 'items/$id') // item $id -> itemFull
// c(GET, 'items/$id/closest') // items closest to $id -> itemList hasMany(itemSmall)
// c(GET, 'items/newest/$amount') // newest $amount items -> itemList hasMany(itemSmall)
