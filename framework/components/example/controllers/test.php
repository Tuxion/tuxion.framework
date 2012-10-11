<?php namespace components\controllers\example;

c()

->pre('Setting the template.', function(){
  
  $this->template('minimal', [
    'menu_id' => $this->input->menu_id
  ]);
  
})

;

c(GET, 'test')->end('Loading the test page.', function(){
  
  $this->output->set([
    'foo' => 'Test page!',
    'nyerk' => 'add another segment to the url'
  ]);
  
})->post('Adding some stuff.', function(){
  
  $nyerk = $this->output->nyerk;
  
  $nyerk->set(ucfirst($nyerk->get()).'.');
  
});

// c(GET, 'hoi')->run(function(){
  
//   c(GET, 'test')->end('Hoi test.', function(){
    
//     $this->output->set([
//       'foo' => 'bar',
//       'nyerk' => $input
//     ]);
    
//   });
  
// });

c(GET, 'test/$input')->end('Testing something.', function($input){
  
  $this->output->set([
    'foo' => 'bar',
    'nyerk' => $input
  ]);
  
});
