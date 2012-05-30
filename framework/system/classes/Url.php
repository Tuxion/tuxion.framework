<?php namespace classes;

class Url 
{
  
  //Constants.
  const
    ALL = 0,
    SCHEME = 1,
    DOMAIN = 2,
    PORT = 4,
    PATH = 8,
    QUERY = 16,
    ANCHOR = 32;
    
  //Private static properties.
  private static
    $defaults=[
      'build_on_redirect' => false,
      'discard_old_querystring' => false
    ];
  
  //Public properties.
  public
    $input,
    $options=[],
    $output='',
    $segments,
    $meta,
    $data;
  
  //Creates a new $this instance.
  public static function create($url, $discard_old_querystring=null, $build_on_redirect=null)
  {
    
    return new self($url, array(
      'discard_old_querystring' => (is_null($discard_old_querystring)
        ? self::$defaults['discard_old_querystring']
        : $discard_old_querystring
      ),
      'build_on_redirect' => (is_null($build_on_redirect)
        ? self::$defaults['build_on_redirect']
        : $build_on_redirect
      )
    ));
    
  }
  
  //Parses a string as URL and returns an array of all "URL-like" segments present.
  public static function parse($url, $flags = 0)
  {
    
    //We require a string.
    if(!is_string($url)){
      throw new \exception\InvalidArgument('Expecting $url to be string. %s given.', ucfirst(typeof($url)));
    }
    
    //This is the regular expression that parses the URL. It allows every segment to be optional.
    $regex =
      "~^(?!&)". //url can not start with '&'
      "(?:(?<scheme>[^:/?#]+)(?=://))?". //scheme
      "(?:\://)?". //thingy
      "(?:(?<!\?)(?<=\://)(?<domain>(?:[a-zA-Z0-9\-]+)(?:\.[a-zA-Z0-9\-]+)*))?". //domain
      "(?:(?<!\?)(?<port>\:\d+))?". //port
      "(?:(?<!\?)(?<path>[^?#]*))?". //path
      "(?:\?(?<query>(?:[^#]+(?:=[^#]+)?)(?:&(?:amp;)?[^#]+(?:=[^#]+)?)*))?". //query
      "(?:#?(?<anchor>.+))?$~"; //anchor
    
    if(!preg_match($regex, $url, $segments)){
      throw new \exception\Unexpected("Oh no! The URL that was given could not be parsed.");
      return false;
    }

    foreach($segments as $key => $val)
    {

      if(is_numeric($key)
        || (empty($val)
          && !checkbit(constant('self::'.strtoupper($key)), $flags)
        )
        || ($flags > 0
          && !checkbit(constant('self::'.strtoupper($key)), $flags)
        )
      ){
        unset($segments[$key]);
      }

    }

    if(count_bits($flags) == 1){
      return (count($segments) == 1 ? current($segments) : false);
    }

    else{
      return $segments;
    }

  }
  
  //We expect a URL in the form of a string to be given to the constructor.
  public function __construct($url=null, array $options=[])
  {
    
    //Validate $url.
    if(!is_string($url)){
      throw new \exception\InvalidArgument('Expecting $url to be string. %s given.', ucfirst(typeof($url)));
    }
    
    //Set the input and build the URL.
    $this->input = $url;
    $this->options = $options;
    $this->segments = new \classes\ArrayObject([]);
    $this->meta = new \classes\ArrayObject([]);
    $this->data = Data([]);
    $this->_build();
    $this->segments->setArrayPermissions(1,0,0);
    $this->meta->setArrayPermissions(1,0,0);
    
  }
  
  public function __toString()
  {
    
    return $this->output;
    
  }
  
  public function compare(Url $url)
  {
    
    return $this->output === $url->output;
    
  }
  
  private function _build()
  {
    
    //Merge with defaults.
    $options = array_merge(self::$defaults, $this->options);
    
    //Get the URL object that represents the current request URL.
    $old_url = (($options['build_on_redirect'] && tx('Router')->redirected())
      ? tx('Router')->redirect_url
      : (tx('Request')->url instanceof self ? tx('Request')->url : false)
    );
  
    //Parse input to segments.
    $segments = self::parse($this->input);
    
    //scheme segment.
    $this->segments->scheme = (array_key_exists('scheme', $segments)
      ? $segments['scheme']
      : ($old_url ? $old_url->segments->scheme : 'http')
    );

    //Domain segment.
    $this->segments->domain = (array_key_exists('domain', $segments)
      ? $segments['domain']
      : ($old_url ? $old_url->segments->domain : '')
    );
    
    //Meta: external.
    $this->meta->external = (tx('Request')->url instanceof self
      ? (tx('Request')->url->segments->domain !== $this->segments->domain)
      : false
    );
    
    //Detect what to do with the path segment, if it's been given.
    if(array_key_exists('path', $segments))
    {
      
      //External paths are always absolute.
      if($this->meta->external === true){
        $this->segments->path = $segments['path'];
      }
      
      //Internal paths can be absolute or relative.
      else
      {
        
        //If it is absolute.
        if($segments['path']{0} === '/')
        {
          
          //Should we make it relative to the root?
          if(array_key_exists('domain', $segments))
          {
            
            //Set the path.  
            $this->segments->path = $segments['path'];
            
            //This could be a different subsystem.
            if(strpos($segments['path'], tx('Config')->urls->path) !== 1){
              $this->meta->external = true;
            }
            
          }
          
          //Otherwise we make it relative to the system base.
          else{
            $this->segments->path = '/' . tx('Config')->urls->path . $segments['path'];
          }
          
        }
        
        //If it is relative, we make it relative to the current URL path.
        else{
          $this->segments->path = ($old_url ? $old_url->segments->path : '').'/'.$segments['path'];
        }
        
      }
      
    }
    
    //No path segment given.
    else{
      $this->segments->path = ($this->meta->external === true
        ? '/'
        : ($old_url ? $old_url->segments->path : '/')
      );
    }
    
    //Parse the data that has been given as query string.
    parse_str((array_key_exists('query', $segments) ? $segments['query'] : ''), $given_data);
    
    //Should we skip merging?
    if($options['discard_old_querystring'] || $this->meta->external == true){
      $data = $given_data;
    }
    
    //Should we merge?
    else{
      $data = array_merge_recursive(($old_url ? $old_url->data->toArray() : []), $given_data);
    }
    
    //Normalize data.
    foreach($data as $key => $val)
    {
      
      //Check if we are to delete this value.
      if(($val==='NULL' || is_null($val)) || is_numeric($key)){
        unset($data[$key]);
      }
      
      //Check if we explicitly are to keep this value.
      if($val==='KEEP'){
        $keep[] = $key;
        $data[$key] = data_of($old_url->data->extract($key));
      }
      
      //Check if the value was empty. If it was we will interpret the key as flag, and set it to "1".
      if(empty($val)){
        $data[$key] = '1';
      }
      
    }
    
    //Set the query and the data.
    if(count($data) > 0){
      $this->segments->query = http_build_query($data, null, '&');
      $this->data->set($data);
    }
    
    //Anchor segment.
    if(array_key_exists('anchor', $segments)){
      
      //Prettify the hash "query string".
      $anchor = str_replace(array('&', '='), array('/', '/'), $segments['anchor']);
      
      //Add a "pretty" hash slash if not already present.
      if($anchor{0} !== '/'){
        $anchor = "$anchor/";
      }
      
      //Set the anchor.
      $this->segments->anchor = $anchor;
      
    }
    
    //Create output.
    $this->output = (
      $this->segments->scheme.'://'.
      $this->segments->domain.
      $this->segments->path.
      (isset($this->segments->query) ? '?'.$this->segments->query : '').
      (isset($this->segments->anchor) ? '#'.$this->segments->anchor : '')
    );
    
  }
  
}
