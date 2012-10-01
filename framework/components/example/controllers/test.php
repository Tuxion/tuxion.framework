<?php namespace components\controllers\example;

c()->pre('Setting the template.', function(){
  
  $this->template('minimal', [
    'menu_id' => $this->input->menu_id
  ]);
  
});

c(GET, 'test')->end('Loading the test page.', function(){
  
  $this->output->set([
    'foo' => 'Test page!',
    'nyerk' => 'Add another segment to the url.'
  ]);
  
});

c(GET, 'test/$input')->end('Testing something.', function($input){
  
  $this->output->set([
    'foo' => 'bar',
    'nyerk' => $input
  ]);
  
});

c(GET, 'test/foobar')->end('Asd.', function(){
  echo 'asd';
});
