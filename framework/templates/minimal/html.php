<!DOCTYPE html>

<html>
  
  <head>
    
    <title><?=$this->getTitle()?></title>
    
  </head>
  
  <body>
    
    <?=$content?>
    
    <div>
    <?=$this->request('com/example/test/whoo1')?>
    <?=$this->request('com/example/test/123')?>
    <?=$this->request('com/example/test/HOI')?>
    </div>
    
  </body>
  
</html>
