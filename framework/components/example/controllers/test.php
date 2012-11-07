<?php namespace components\controllers\example;

route(GET, '$int/$[A-Za-z+]')->end('Generating data for the test page.', function($id, $word){
  $this->setTemplate('minimal');
  $this->output($this->input()->merge(['foo' => "$word ($id)"]));
});
