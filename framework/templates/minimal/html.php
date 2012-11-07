<!DOCTYPE html>

<html>
  
  <head>
    
    <title><?=$this->getTitle()?></title>
    
  </head>
  
  <body>
    
    <?=$this?>
    
    <div>
    <?=$this->request('com/example/1/Hoi')?>
    <?=$this->request('com/example/2/Doei')?>
    <?=$this->request('com/example/3/Wereld')?>
    </div>
    
  </body>
  
</html>
