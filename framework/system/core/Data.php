<?php namespace core;

class Data
{

	public 
    $post,
    $get,
    $session,
    $server,
    $files,
    $env,
    $cookie;
  
  public function init()
	{
    
    $datatypes = array(
			'post'=>$_POST,
			'get'=>$_GET,
      'session'=>((isset($_SESSION) && is_array($_SESSION)) ? $_SESSION : []),
			'server'=>$_SERVER,
			'files'=>$_FILES,
			'env'=>$_ENV,
			'cookie'=>$_COOKIE
		);
	
		//Filter all data and put them in appropiate arrays.
		foreach($datatypes as $type => $global)
		{
    
      $global = $this->xssClean($global);
      $this->{$type} = Data($global);
      
		}
    
		//Unset the data arrays to prevent direct use.
		unset($_POST, $_GET, $_SERVER, $_FILES, $_ENV, $_COOKIE, $_REQUEST);
    $_SESSION = array();
		
	}
	
  // http://svn.bitflux.ch/repos/public/popoon/trunk/classes/externalinput.php
  public function xssClean($str)
	{
		
    // +----------------------------------------------------------------------+
		// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
		// +----------------------------------------------------------------------+
		// | Licensed under the Apache License, Version 2.0 (the "License");      |
		// | you may not use this file except in compliance with the License.     |
		// | You may obtain a copy of the License at                              |
		// | http://www.apache.org/licenses/LICENSE-2.0                           |
		// | Unless required by applicable law or agreed to in writing, software  |
		// | distributed under the License is distributed on an "AS IS" BASIS,    |
		// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
		// | implied. See the License for the specific language governing         |
		// | permissions and limitations under the License.                       |
		// +----------------------------------------------------------------------+
		// | Author: Christian Stocker <chregu@bitflux.ch>                        |
		// +----------------------------------------------------------------------+
		//
		// Kohana Modifications:
		// * Changed double quotes to single quotes, changed indenting and spacing
		// * Removed magic_quotes stuff
		// * Increased regex readability:
		//   * Used delimeters that aren't found in the pattern
		//   * Removed all unneeded escapes
		//   * Deleted U modifiers and swapped greediness where needed
		// * Increased regex speed:
		//   * Made capturing parentheses non-capturing where possible
		//   * Removed parentheses where possible
		//   * Split up alternation alternatives
		//   * Made some quantifiers possessive
		// * Handle arrays recursively

		if (is_array($str) OR is_object($str))
		{
			foreach ($str as $k => $s)
			{
				$str[$k] = $this->xssClean($s);
			}

			return $str;
		}

		// Remove all NULL bytes
		$str = str_replace("\0", '', $str);

		// Fix &entity\n;
		$str = str_replace(['&amp;','&lt;','&gt;'], ['&amp;amp;','&amp;lt;','&amp;gt;'], $str);
		$str = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $str);
		$str = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $str);
		$str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$str = preg_replace('#(?:on[a-z]+|xmlns)\s*=\s*[\'"\x00-\x20]?[^\'>"]*[\'"\x00-\x20]?\s?#iu', '', $str);

		// Remove javascript: and vbscript: protocols
		$str = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $str);
		$str = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $str);
		$str = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $str);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$str = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#is', '$1>', $str);
		$str = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#is', '$1>', $str);
		$str = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#ius', '$1>', $str);

		// Remove namespaced elements (we do not need them)
		$str = preg_replace('#</*\w+:\w[^>]*+>#i', '', $str);

		do
		{
			// Remove really unwanted tags
			$old = $str;
			$str = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $str);
		}
		while ($old !== $str);

		return $str;
    
	}
  
  // destructor puts session array back in to $_SESSION and stores postdata in session if this page redirects
	public function __destruct()
	{
    
    $_SESSION = $this->session->toArray();
    
  }
	
}


