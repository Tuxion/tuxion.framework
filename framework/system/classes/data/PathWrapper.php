<?php namespace classes\data;

class PathWrapper extends StringWrapper
{
  
  //Compares if the key matches the value. Returns true if it does.
  private static function compare($key, $value)
  {
    
    //If the first character of the given part is a dollar sign, test for regular expressions.
    if($key{0} == '$')
    {
      
      
      //Are we dealing with a character class?
      if($key{1} == '['
      && substr($key, -1) == ']'
      && substr_count($key, '[') == 1
      && substr_count($key, ']') == 1
      ){
        
        //Test is the value matches the character class.
        if(preg_match('~^'.substr($key, 1).'+$~', $value) !== 1){
          return false;
        }
        
      }
      
      //No character class.
      else
      {
        
        //Are we dealing with a data-preset?
        switch(substr($key, 1))
        {
          case 'int':   $test = '~^[0-9]+$~';         break;
          case 'float': $test = '~^[0-9]+\.[0-9]+$~'; break;
          case 'word':  $test = '~^\w+$~';            break;
          case 'title': $test = '~^[\w\-\+]+$~';      break;
          default:      $test = false;                break;
        }
        
        //If a data-preset was found, do the matching.
        if($test && (preg_match($test, $value) !== 1)){
          return false;
        }
        
      }
      
      //All good.
      return true;
      
    }
    
    //The parts must be equal.
    if($key !== $value){
      return false;
    }
    
    //All good.
    return true;
    
  }
  
  //Public properties.
  public
    $parts=[];
  
  //Return the different parts of the path.
  public function getParts($wrap_file = false)
  {
    
    //Parse the path.
    $this->parsePath();
    
    //Initiate the return value.
    $return = new ArrayWrapper($this->parts);
    
    //Post-wrap the file?
    if($wrap_file){
      $return->end();
      $k = $return->key();
      $return->arraySet($k, $this->getFile());
    }
    
    //Return the parts.
    return $return;
    
  }
  
  //Get the part at the given index. False of non-existent.
  public function getPart($index)
  {
    
    //Parse the path.
    $this->parsePath();
    
    //Return false?
    if(!array_key_exists($index, $this->parts)){
      return false;
    }
    
    //Return the part.
    return $this->parts[$index];
    
  }
  
  //Return the number of parts.
  public function countParts()
  {
    
    return substr_count($this->value, '/')+1;
    
  }
  
  //Return the last part of the path wrapped in a FileWrapper.
  public function getFile()
  {
    
    //Parse the path.
    $this->parsePath();
    
    //Return the last part.
    return new FileWrapper($this->parts[$this->countParts()-1]->get());
    
  }
  
  //Get the path without the last part.
  public function getDir()
  {
    
    //Pre-parse, get a copy of the parts array and pop the last value off.
    $this->parsePath();
    $dir = $this->parts;
    array_pop($dir);
    
    //Give the directory by imploding, wrapping and returning.
    return new self(implode('/', $dir));
    
  }
  
  //Cleans up the path. Removing all sorts of nonsense from it.
  public function clean()
  {
    
    //OK!
    if($this->value === ''){
      return clone $this;
    }
    
    //Put the value in a variable that we're going to clean.
    $path = $this->value;
    
    //Do the following.
    do{
    
      //Remember what the path was like before we started mangling it.
      $start = $path;
    
      //Decode.
      $path = urldecode($path);
  
      //Replace backward slashes.
      $path = str_replace('\\', '/', $path);
      
      //Trim double slashes.
      $path = preg_replace('~/+~', '/', $path);
      
      //Replace /../ stuff.
      $path = preg_replace('~(?=\/)\.\.*/~', './', $path);
      
      //Replace spaces.
      $path = str_replace(' ', '+', $path);
      
      //Replace illegal characters.
      $path = preg_replace('~[#@?!]+~', '-', $path);
      
      //Explode into segments.
      $segments = explode('/', $path);
      
      //Validate and normalize segments.
      foreach(array_keys($segments) as $key)
      {
        
        //Keep a reference.
        $segment =& $segments[$key];
        
        //Wrap the segment.
        $segment = wrap($segment)
        
        //Trim off the illegal characters off the end.
        ->trim(RIGHT, '.')
        
        //Trim off the illegal characters off the end and start.
        ->trim('+')
        
        //Get raw value.
        ->unwrap();
        
        //Unset the reference.
        unset($segment);
        
      }
      
      //Use the normalized segments as path.
      $path = implode('/', $segments);
      
    }
    
    //And keep repeating it as long as it is still changing stuff.
    while($path !== $start);
    
    //Return the new path.
    return new self($path);
    
  }
  
  //Merges the given path with this one.
  public function merge($input, $clear_ext = false)
  {
    
    //Get path from PathWrapper.
    if($input instanceof PathWrapper){
      return $this->merge($input->get());
    }
    
    //Extract raw input.
    raw($input);
    
    //Given as string.
    if(is_string($input)){
      $path = $input;
    }
    
    //Given as array.
    elseif(is_array($path)){
      $path = implode('/', $path);
    }
    
    //Given weirdly.
    else{
      throw new \exception\InvalidArgument('Can not merge a(n) %s into a path.', typeof($input));
    }
    
    //Wrap the given path
    $wrapped = path($path);
    
    //Get the right extension.
    $ext = ($clear_ext
      ? ($wrapped->getFile()->hasExt() ? $wrapped->getFile()->getExt()->unwrap() : '')
      : ($wrapped->getFile()->hasExt() ? $wrapped->getFile()->getExt()->unwrap() : ($this->getFile()->hasExt()
        ? $this->getFile()->getExt()->unwrap()
        : ''
      ))
    );
    
    //Strip extension.
    $path = $wrapped->getDir().'/'.$wrapped->getFile()->getName();
    
    //Is it a part?
    $part = $wrapped->getFile()->isPart();
    
    //Prepend our own path?
    if($path{0} !== '/'){
      $path = $this->getDir().'/'.$this->getFile()->getName().'/'.$path;
    }
    
    //Create the new path.
    return new self(''
      . ($path)
      . ($part ? '.part' : '')
      . (empty($ext) ? '' : ".$ext")
    );
    
  }
  
  //Returns true if the given path matches this one.
  public function isMatch(self $path)
  {
    
    //If the given path is longer than this one, it's a mismatch.
    if($path->countParts() > $this->countParts()){
      return false;
    }
    
    //Match file?
    $match_file = ($path->countParts() === $this->countParts());
    
    //Get the local and the given parts of the directory. Cut off the file?
    if($match_file){
      $lparts = $this->getDir()->getParts();
      $gparts = $path->getDir()->getParts();
    }
    
    //Don't cut off the file. There is no file.
    else{
      $lparts = $this->getParts();
      $gparts = $path->getParts();
    }
    
    //Prepare for iteration.
    $lpart = $lparts->reset();
    $gpart = $gparts->reset();
        
    //Iterate over the given parts.
    do
    {
      
      //Get raw values.
      raw($lpart, $gpart);
      
      //Do the comparison.
      if(!self::compare($gpart, $lpart)){
        return false;
      }
      
    }
    
    //Get the next set of parts.
    while(($lpart = $lparts->next()) && ($gpart = $gparts->next()));
    
    //Match files?
    if($match_file)
    {
      
      //Get local and given files.
      $lfile = $this->getFile();
      $gfile = $path->getFile();
      
      //The names must be a match.
      if(!self::compare($gfile->getName()->get(), $lfile->getName()->get())){
        return false;
      }
      
      //The mime-type of the local must be the same of that of the given.
      if($gfile->hasExt() && ($gfile->getMime()->get() !== $lfile->getMime()->get())){
        return false;
      }
      
      //The local file must be a .part of the given file is a .part.
      if($gfile->isPart() && !$lfile->isPart()){
        return false;
      }
      
    }
    
    return true;
    
  }
  
  //Use this path as "values" and return an ArrayWrapper of them, using the given keys.
  public function getValues(self $keys)
  {
    
    //Get actual array of keys and values.
    $keys = $keys->getParts(true);
    $values = $this->getParts(true);
    
    //Prepare for iteration.
    $key = $keys->reset();
    $value = $values->reset();
    
    //Initiate output array.
    $parameters = [];
    
    //Do the iteration.
    do
    {
      
      //Get the name of the key.
      if($key instanceof FileWrapper){
        $key = $key->getName();
      }
      
      //Get the name of the value.
      if($value instanceof FileWrapper){
        $value = $value->getName();
      }
      
      //Get the raw values.
      raw($key, $value);
      
      //Are we even dealing with a parameter?
      if($key{0} != '$'){
        continue;
      }
      
      //Add the value to our parameters.
      $parameters[substr($key, 1)] = $value;
      
    }
    
    //Get the next pair.
    while(($key = $keys->next()) && ($value = $values->next()));
    
    //Output the parameters.
    return $parameters;
    
  }
  
  //Parse the path and cache the results.
  private function parsePath()
  {
    
    //Do nothing if we already did this.
    if(count($this->parts)){
      return $this;
    }
    
    //Parse the path.
    $parts = explode('/', $this->value);
    
    //Wrap and store all the parts.
    foreach($parts as $key => $value){
      $this->parts[$key] = new StringWrapper($value);
    }
    
    //Enable chaining.
    return $this;
    
  }
  
}
