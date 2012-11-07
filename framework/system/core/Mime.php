<?php namespace core;

class Mime
{
  
  public $mimes = [];
  
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
  
  public function getMime($type)
  {
    
    $matches = array_search_recursive($type, $this->mimes);
    
    if($matches === false){
      throw new \exception\Nonexistent('Could not find a mime for file-type "%s".', $type);
    }
    
    return $matches[0];
    
  }
  
  public function getType($mime)
  {
    
    if(!array_key_exists($mime, $this->mimes)){
      throw new \exception\Nonexistent('Could not find a type for mime "%s".', $mime);
    }
    
    return $this->mimes[$mime][0];
    
  }
  
  //Return all types associated with the given mime.
  public function getTypes($mime)
  {
      
    if(!array_key_exists($mime, $this->mimes)){
      throw new \exception\Nonexistent('Could not find the types for mime "%s".', $mime);
    }
    
    return $this->mimes[$mime];
    
  }
  
}
