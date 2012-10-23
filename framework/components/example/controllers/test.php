<?php namespace components\controllers\example;

route()

->pre('Setting the template.', function(){
  
  $this->template('minimal', [
    'menu_id' => $this->input->menu_id
  ]);
  
})

;

route(GET, 'test')

->pre('Checking for permissions.', function(){
  $this->permissions('eat_pie');
})

->end('Loading the test page.', function(){
  
  $this->output->set([
    'foo' => 'Test page!',
    'nyerk' => 'add another segment to the url'
  ]);
  
})

->post('Adding some stuff.', function(){
  
  $nyerk = $this->output->nyerk;
  
  $nyerk->set(ucfirst($nyerk->get()).'.');
  
});

// route(GET, 'hoi')->run(function(){
  
//   route(GET, 'test')->end('Hoi test.', function(){
    
//     $this->output->set([
//       'foo' => 'bar',
//       'nyerk' => $input
//     ]);
    
//   });
  
// });

route(GET, 'test/$int')->end('Testing something.', function($input){
  
  $this->output->set([
    'foo' => 'int',
    'nyerk' => $input
  ]);
  
});
