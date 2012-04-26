<?php namespace components\controllers\example;

c('test')->run(function(){
  
  c('nyerk')->run(function(){
    
    c('snarl')->run(function(){
      
      trace(c()->base);
      
    });
    
  });
  
});

trace(c()->base);

trace(c('/asd')->base);

exit;
