<?php namespace components\controllers\example;

//trace( $file, $this, $R, $R->is('/example') );

// trace($R);
// trace($R);
// trace($R);
// trace($R);
// trace($R);

$R('test')
   ->end('An endpoint in a reroute route.', function(){
      throw new \exception\Exception('Endpoint test reached!');
   });

$R('test')
  ->pre('Rerouting to someplace else.', function(){
    $this->reroute('nyerk/foo');
  });
  
$R->with('nyerk/foo', function(){
  
  $this('bar')->end('Display the right bar.', function(){
    tx('Log')->message(__NAMESPACE__, 'Endpoint reached.');
    echo 'Hello!';
    //throw new \exception\Exception('Endpoint bar reached!');
  });
  
});
