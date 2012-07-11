<!DOCTYPE html>

<html>
  
  <head>
    
    <title><?=$this->getTitle()?></title>
    
  </head>
  
  <body>
    
    <?=$content?>
    
    <div>
    <?=$this->request('com/example/test/whoo', Data([]))?>
    </div>
    
  </body>
  
</html>
