<!DOCTYPE html>

<html>
  
  <head>
    
    <title><?=$t->getTitle()?></title>
    
  </head>
  
  <body>
    
    <?=$t?>
    
    <div>
    <?=$t->request('com/example/1/Hoi')?>
    <?=$t->request('com/example/2/Doei')?>
    <?=$t->request('com/example/3/Wereld')?>
    </div>
    
  </body>
  
</html>
