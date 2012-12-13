<?php namespace core;

class Mime
{
  
  private $mimes = [];
  
  //Initialize.
  public function init()
  {
      
    //Enter a log entry.
    tx('Log')->message($this, 'Mime class initializing.');
    
    //Get mimes from database.
    tx('Sql')->exe('
      SELECT mt.name AS mime, me.name AS ext FROM #system_mime_types AS `mt` 
      INNER JOIN #system_mime_extensions AS `me` ON mt.id = me.type_id
    ')
    
    //Iterate the results.
    ->each(function($row){
      
      //Create the mime?
      if(!array_key_exists($row->mime, $this->mimes)){
        $this->mimes[$row->mime] = [];
      }
      
      //Add extension to the mime.
      $this->mimes[$row->mime][] = $row->ext;
      
    });
    
    //Enter a log entry.
    tx('Log')->message($this, 'Mime class initialized.');
    
  }
  
  //Return the mime belonging to the given file-type.
  public function getMime($type)
  {
    
    return wrap($this->mimes)->searchRecursive($type)->alt([false])[0];
    
  }
  
  //Return the entire mimes array.
  public function getMimes()
  {
    
    return $this->mimes;
    
  }
  
  //Return the first file-type associated with the given mime.
  public function getType($mime)
  {
    
    return wrap($this->mimes)->wrap($mime)->alt([false])[0];
    
  }
  
  //Return all types associated with the given mime.
  public function getTypes($mime)
  {
    
    return wrap($this->mimes)->wrap($mime)->alt(false)->unwrap();
    
  }
  
}
