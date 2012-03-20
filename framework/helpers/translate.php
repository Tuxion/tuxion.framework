<?php if(!defined('TX')) die('No direct access.');

function __($phrase, $only_return = false)
{
  //load ini file
  $lang_file = PATH_SITE.'/languages/'.LANGUAGE_CODE.'.ini';
  if(!is_file($lang_file)){
    throw new \exception\FileMissing('The file \'%s\' can not be found.', $lang_file);
  }

  //parse ini file
  $ini_arr = parse_ini_file($lang_file);
  
  //translate
  if(array_key_exists($phrase, $ini_arr)){
    $phrase = $ini_arr[$phrase];
  }
  
  //return (translated) phrase
  if($only_return){
    return $phrase;
  }else{
    echo $phrase;
  }
}

function ___($phrase)
{
  return __($phrase, 1);
}