<?php namespace components\controllers\example;

route(GET, '$int/$[A-Za-z+]')->end('Generating data for the test page.', function($id, $word){
  ob_start();
  trace($this->component());
  $a = ob_get_clean();
  $this->setTemplate('minimal');
  $this->output(array_merge($this->input(), ['foo' => "$word ($id)", 'a' => $a]));
});

route(GET, '/derp');
