<?php namespace components\controllers\example;

c(GET, 'test')->end('Testing something.', function(){
});


// c(GET, 'test/$something')->end('Testing something.', function($something){
//   $this->output['test'] = $something;
// });

// c(GET, 'page/$id')->end('Loading page.', function(){
  
//   // $this->output = $this->fetchAll('Book')->go();
  
//   // $this->template('page', [
//   //   'menu_id' => 2,
//   //   'menu_max_depth' => 4
//   // ]);
  
// });

// c(GET, 'items') // all items -> itemList hasMany(itemSmall)
// c(GET, 'items/$id') // item $id -> itemFull
// c(GET, 'items/$id/closest') // items closest to $id -> itemList hasMany(itemSmall)
// c(GET, 'items/newest/$amount') // newest $amount items -> itemList hasMany(itemSmall)
