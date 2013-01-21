<?php namespace classes\data;

class UrlWrapper extends StringWrapper
{
  
  const
    
    //Merging options.
    MERGE_HTTP_QUERY_OVERRIDE = 0b001,
    MERGE_PATH_DROP_EXT       = 0b010,
    MERGE_PATH_PREPEND_ROOT   = 0b100,
    
    //Matching patterns.
    MATCH_DOMAIN = '(?:[a-zA-Z0-9\-]+)(?:\.[a-zA-Z0-9\-]+)+',
    MATCH_PATH   = '[a-zA-Z0-9$\-.+!*\'(),&;/@]*';
  
  //Parse the given URL and return an array of segments.
  public static function parseUrl($input)
  {
    
    //Wrap the input.
    $wrapped = wrap($input);
    
    //Parse it.
    $segments = $wrapped->parse(
      '~^'.                                  //Begin: regular expression.
      '(?:(?<scheme>.+?):)'.                 //Scheme (required).
      '(?://'.                               //Begin: scheme data (optional).
        '(?:'.                               //Begin: authentication (optional).
          '(?:(?<username>.*?))'.            //User name (required).
          '(?::(?<password>.*?))?'.          //Password (optional).
        '@)?'.                               //End: authentication.
        '(?<domain>'.self::MATCH_DOMAIN.')'. //Domain (required).
        '(?::(?<port>\d+))?'.                //Port (optional).
      '(?:(?<path>'.self::MATCH_PATH.'))?'.  //Path (optional).
      ')?'.                                  //End: scheme data.
      '(?<specific>.*)'.                     //Protocol-specific data.
      '$~'                                   //End: Regular expression.
    )
    
    //Assign it to a variable.
    ->put($segments);
    
    //Check if the URL was valid.
    if($wrapped->failure()){
      throw new \exception\Parsing('The URL could not be parsed.');
    }
    
    //Return the array of segments.
    return $segments->filter(function($v, $k){
      return ! is_numeric($k);
    })->unwrap();
    
  }
  
  //Parse the given URL, but with all fields optional.
  public static function parsePartialUrl($input)
  {
    
    //Wrap the input.
    $wrapped = wrap($input);
    
    //Parse it.
    $segments = $wrapped->parse(
      '~^(?!&)'.                             //Begin: regular expression.
      '(?:(?<scheme>[^:/?#]+):)?'.           //Scheme.
      '(?://)?'.                             //Scheme data opener.
      '(?:'.                                 //Begin: authentication.
        '(?:(?<username>.*?))?'.             //User name.
        '(?::(?<password>.*?))?'.            //Password.
      '@)?'.                                 //End: authentication.
      '(?:(?<=//|@)'.                        //Begin: domain.
        '(?<domain>'.self::MATCH_DOMAIN.')'. //Domain.
        '(?::(?<port>\d+))?'.                //Port.
      ')?'.                                  //End: domain.
      '(?:(?<path>'.self::MATCH_PATH.'))?'.  //Path.
      '(?<specific>.*)'.                     //Protocol-specific data.
      '$~'                                   //End: Regular expression.
    );
    
    //Check if the URL was valid.
    if($wrapped->failure()){
      throw new \exception\Parsing('The semi-URL could not be parsed.');
    }
    
    //Return the array of segments.
    return $segments->filter(function($v, $k){
      return ! is_numeric($k);
    })->unwrap();
    
  }
  
  //Parse a HTTP protocol specific string.
  public function parseHttpData($input)
  {
    
    //Prepare variables needed for parsing.
    $s = ini_get('arg_separator');
    $s = (is_string($s) ? $s : '&');
    $wrapped = wrap($input);
    
    //Parse for query string and anchor.
    $segments = $wrapped->parse(
      '~^'.                                           //Begin: regular expression.
      '(?:\?(?<query>(?:[^#]+?)(?:'.$s.'[^#]+?)*))?'. //Query string (optional).
      '(?:#(?<anchor>.*))?'.                          //Anchor (optional).
      '$~'                                            //End: regular expression.
    );
    
    //If it failed to parse, we'll throw an exception.
    if($wrapped->failure()){
      throw new \exception\Parsing('Failed to parse HTTP specific data in the URL.');
    }
    
    //Return the parsed result.
    return $segments
    
    //After setting the defaults..
    ->defaults([
      'query' => '',
      'anchor' => ''
    ])
    
    //And filtering out the numeric keys..
    ->filter(function($v, $k){
      return ! is_numeric($k);
    })
    
    //And unwrapping it.
    ->unwrap();
    
  }
  
  //Protected properties.
  protected
    $parsed=[],
    $merge_options=0;
  
  //Use magic to get parsed data.
  public function __call($method, $args)
  {
    
    //The method must be like: "getSomething".
    if(preg_match('~^get([A-Z]\w*)$~', $method, $matches) == 0){
      throw new \exception\NonExistent('Method "%s" does not exist.', $method);
    }
    
    //Extract the key and pre-parse the data.
    $key = strtolower($matches[1]);
    $this->parseAndCache();
    
    //The requested data must be present.
    if(!array_key_exists($key, $this->parsed)){
      throw new \exception\NotFound('Could not find "%s" amongst the parsed data.', $key);
    }
    
    //Return the wrapped data.
    return wrapRaw($this->parsed[$key]);
    
  }
  
  //Return all the parsed data.
  public function getParsed()
  {
    
    //Pre-parse the data.
    $this->parseAndCache();
    
    //Return the parsed data.
    return new ArrayWrapper($this->parsed);
    
  }
  
  //Set the options that are to be used when this URL is merged with another one.
  public function setMergeOptions($flags)
  {
    
    //Validate argument.
    if(!is_int($flags)){
      throw new \exception\InvalidArgument(
        'Expecting $flags to be of type integer. %s given.', ucfirst(typeof($flags))
      );
    }
    
    //Set.
    $this->merge_options = $flags;
    
    //Enable chaining.
    return $this;
    
  }
  
  //Accepts partial URL data, in the form of a string, array or UrlWrapper and creates a new UrlWrapper.
  public function merge($input)
  {
    
    //Get segments from a UrlWrapper.
    if($input instanceof UrlWrapper){
      return $this->merge($input->getParsed()->unwrap());
    }
    
    //From here any input is more useful in its raw format.
    raw($input);
    
    //Get segments from an array.
    if(is_array($input)){
      $new = $input;
    }
    
    //Get segments from a string.
    elseif(is_string($input)){
      $new = self::parsePartialUrl($input);
    }
    
    //What ARE you doing..?
    else{
      throw new \exception\InvalidArgument('Can not merge a(n) %s into a URL.', typeof($input));
    }
    
    //Filter out empty values from the segments.
    $new = wrap($new)->filter(function($v){return !empty($v);});
    
    //Get our own segments.
    $this->getParsed()
    
    //Store it in a variable.
    ->put($old)
    
    //Filter out the empty values.
    ->filter(function($v){return !empty($v);})
    
    //Merge the other segments.
    ->merge($new)
    
    //Fill in the gaps.
    ->defaults($old)
    
    //Assign to a variable.
    ->put($merged);
    
    //Properly merge the path.
    if($new->arrayExists('path')){
      $merged['path'] = $old['path']->merge(
        $new->path,
        wrap($this->merge_options)->hasBit(self::MERGE_PATH_DROP_EXT),
        wrap($this->merge_options)->hasBit(self::MERGE_PATH_PREPEND_ROOT)
      );
    }
    
    //Clean the path.
    $merged['path'] = $merged['path']->clean();
    
    //Create the method name for merging protocol-specific data.
    $merge_method = 'merge'.ucfirst($merged['scheme']);
    
    //Merge using the above method?
    if(method_exists($this, $merge_method) && $new->arrayExists('specific')){
      $merged['specific'] = call_user_func([$this, $merge_method], $new->specific);
    }
    
    //Create the authentication string.
    $authentication = ($merged['username'].(empty($merged['password']) ? '' : ":{$merged['password']}"));
    
    //Create the scheme data string.
    $scheme_data = (''
      . (empty($authentication) ? '' : "$authentication@")
      . ($merged['domain'])
      . (empty($merged['port']) ? '' : ":{$merged['port']}")
      . ($merged['path'])
    );
    
    //Create and return the new UrlWrapper.
    return new self(''
      . ($merged['scheme'])
      . (':')
      . (empty($scheme_data) ? '' : "//$scheme_data")
      . ($merged['specific'])
    );
    
  }
  
  //Merges the given HTTP data with the present HTTP data and returns the result as string.
  public function mergeHttp($data)
  {
    
    //Get the different segments.
    $segments = wrap(self::parseHttpData($data));
    
    //Prepare query data.
    $old_qd = $this->parsed['query']->getData()->unwrap();
    $new_qd = [];
    
    //Wrap and decode the query string. Then iterate.
    $segments->wrap('query')->decode()->each(function($value, $key)use(&$old_qd, &$new_qd){
      
      
      //Handle "KEEP" values.
      if($value === "KEEP" && array_key_exists($key, $old_qd)){
        $new_qd[$key] = $old_qd[$key];
        return true;
      }
      
      //Handle "DROP" values.
      if($value === "DROP"){
        return true;
      }
      
      //Handle any other values.
      $new_qd[$key] = $value;
      
    });
    
    //Merge?
    if( ! wrap($this->merge_options)->hasBit(self::MERGE_HTTP_QUERY_OVERRIDE)){
      wrap($new_qd)->defaults($old_qd)->put($new_qd);
    }
    
    //Build the query part of the output.
    $output = (empty($new_qd) ? '' : ('?'.http_build_query($new_qd)));
    
    //Get the right anchor.
    $anchor = (empty($segments->anchor) ? $this->parsed['anchor'] : $segments->anchor);
    
    //Build the anchor part of the output.
    $output .= (empty($anchor) ? '' : "#$anchor");
    
    //Return the output.
    return $output;
    
  }
  
  //Alias for merge HTTP.
  public function mergeHttps($data)
  {
    
    return $this->mergeHttp($data);
    
  }
  
  //Parses the input URL and caches the result.
  private function parseAndCache()
  {
    
    //If it has already been cached, do nothing.
    if(count($this->parsed)){
      return $this;
    }
    
    //Parse.
    $this->parsed = self::parseUrl($this->value);
    
    //Wrap the path.
    $this->parsed['path'] = new PathWrapper($this->parsed['path']);
    
    //Create the method name for parsing the protocol-specific data.
    $parse_method = 'parse'.ucfirst($this->parsed['scheme']);
    
    //Parse using the above method?
    if(method_exists($this, $parse_method)){
      call_user_func([$this, $parse_method], $this->parsed['specific']);
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  //Parse HTTP $data and cache it.
  private function parseHttp($data)
  {
    
    //Parse the data and merge it.
    $this->parsed = wrap($this->parsed)->merge(self::parseHttpData($data))->unwrap();
    
    //Wrap the query.
    $this->parsed['query'] = new QueryStringWrapper($this->parsed['query']);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Parse HTTPS. Alias for parseHttp.
  public function parseHttps($data)
  {
    
    return $this->parseHttp($data);
    
  }
  
  //Parse "Callto" links.
  private function parseCallto($data)
  {
    
    //The given data is the phone number.
    $this->parsed['number'] = new PhoneNumberWrapper($data);
    
    //Enable chaining.
    return $this;
    
  }
  
  //Parse "Mailto" links.
  public function parseMailto($data)
  {
    
    //Add the whole data as email address.
    $this->parsed['email'] = new EmailWrapper($data);
    
    //Enable chaining.
    return $this;
    
  }
  
}
