<!DOCTYPE html>

<html>
  
  <head>
    
    <title><?=$this->getTitle()?></title>
    
  </head>
  
  <body>
    
    <?=$content?>
    
    <div>
    <?=$this->request('com/example/test/whoo1')?>
    <?=$this->request('com/example/test/whoo2')?>
    <?=$this->request('com/example/test/whoo3')?>
    <?=$this->request('com/example/test/whoo4')?>
    <?=$this->request('com/example/test/whoo5')?>
    </div>
    
  </body>
  
</html>
