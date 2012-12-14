<?php namespace components\controllers\example;

route(GET, '$int/$[A-Za-z+]')->end('Generating data for the test page.', function($id, $word)use($component){
  $this->setTemplate('minimal');
  $this->output(array_merge($this->input(), ['foo' => "$word ($id)"]));
});

route(GET, '/derp');
