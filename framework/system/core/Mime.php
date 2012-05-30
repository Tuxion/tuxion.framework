<?php namespace core;

class Mime
{
  
  public $mimes = [
    'application/envoy' => ['evy'],
    'application/fractals' => ['fif'],
    'application/futuresplash' => ['spl'],
    'application/hta' => ['hta'],
    'application/internet-property-stream' => ['acx'],
    'application/mac-binhex40' => ['hqx'],
    'application/msword' => ['doc', 'dot'],
    'application/octet-stream' => ['bin', 'class', 'dms', 'exe', 'lha', 'lzh'],
    'application/oda' => ['oda'],
    'application/olescript' => ['axs'],
    'application/pdf' => ['pdf'],
    'application/pics-rules' => ['prf'],
    'application/pkcs10' => ['p10'],
    'application/pkix-crl' => ['crl'],
    'application/postscript' => ['ai', 'eps', 'ps'],
    'application/rtf' => ['rtf'],
    'application/set-payment-initiation' => ['setpay'],
    'application/set-registration-initiation' => ['setreg'],
    'application/vnd.ms-excel' => ['xla', 'xlc', 'xlm', 'xls', 'xlt', 'xlw'],
    'application/vnd.ms-pkicertstore' => ['sst'],
    'application/vnd.ms-pkiseccat' => ['cat'],
    'application/vnd.ms-pkistl' => ['stl'],
    'application/vnd.ms-powerpoint' => ['pot', 'pps', 'ppt'],
    'application/vnd.ms-project' => ['mpp'],
    'application/vnd.ms-works' => ['wcm', 'wdb', 'wks', 'wps'],
    'application/winhlp' => ['hlp'],
    'application/x-bcpio' => ['bcpio'],
    'application/x-cdf' => ['cdf'],
    'application/x-compress' => ['z'],
    'application/x-compressed' => ['tgz'],
    'application/x-cpio' => ['cpio'],
    'application/x-csh' => ['csh'],
    'application/x-director' => ['dcr', 'dir', 'dxr'],
    'application/x-dvi' => ['dvi'],
    'application/x-gtar' => ['gtar'],
    'application/x-gzip' => ['gz'],
    'application/x-hdf' => ['hdf'],
    'application/x-internet-signup' => ['isp', 'ins'],
    'application/x-iphone' => ['iii'],
    'application/x-javascript' => ['js'],
    'application/x-latex' => ['latex'],
    'application/x-msaccess' => ['mdb'],
    'application/x-mscardfile' => ['crd'],
    'application/x-msclip' => ['clp'],
    'application/x-msdownload' => ['dll'],
    'application/x-msmediaview' => ['m13', 'm14', 'mvb'],
    'application/x-msmetafile' => ['wmf'],
    'application/x-msmoney' => ['mny'],
    'application/x-mspublisher' => ['pub'],
    'application/x-msschedule' => ['scd'],
    'application/x-msterminal' => ['trm'],
    'application/x-mswrite' => ['wri'],
    'application/x-perfmon' => ['pma', 'pmc', 'pml', 'pmr', 'pmw'],
    'application/x-php' => ['php', 'phtml'],
    'application/x-pkcs12' => ['p12'],
    'application/x-pkcs12' => ['pfx'],
    'application/x-pkcs7-certificates' => ['spc', 'p7b'],
    'application/x-pkcs7-certreqresp' => ['p7r'],
    'application/x-pkcs7-mime' => ['p7c', 'p7m'],
    'application/x-pkcs7-signature' => ['p7s'],
    'application/x-sh' => ['sh'],
    'application/x-shar' => ['shar'],
    'application/x-stuffit' => ['sit'],
    'application/x-sv4cpio' => ['sv4cpio'],
    'application/x-sv4crc' => ['sv4crc'],
    'application/x-tar' => ['tar'],
    'application/x-tcl' => ['tcl'],
    'application/x-tex' => ['tex'],
    'application/x-texinfo' => ['texi', 'texinfo'],
    'application/x-troff' => ['tr', 't', 'roff'],
    'application/x-troff-man' => ['man'],
    'application/x-troff-me' => ['me'],
    'application/x-troff-ms' => ['ms'],
    'application/x-ustar' => ['ustar'],
    'application/x-wais-source' => ['src'],
    'application/x-x509-ca-cert' => ['crt', 'cer', 'der'],
    'application/ynd.ms-pkipko' => ['pko'],
    'application/zip' => ['zip'],
    'audio/basic' => ['au', 'snd'],
    'audio/mid' => ['rmi', 'mid'],
    'audio/mpeg' => ['mp3'],
    'audio/x-aiff' => ['aif', 'aiff', 'aifc'],
    'audio/x-mpegurl' => ['m3u'],
    'audio/x-pn-realaudio' => ['ra', 'ram'],
    'audio/x-wav' => ['wav'],
    'image/bmp' => ['bmp'],
    'image/cis-cod' => ['cod'],
    'image/gif' => ['gif'],
    'image/ief' => ['ief'],
    'image/jpeg' => ['jpeg', 'jpg', 'jpe'],
    'image/pipeg' => ['jfif'],
    'image/svg+xml' => ['svg'],
    'image/tiff' => ['tif', 'tiff'],
    'image/x-cmu-raster' => ['ras'],
    'image/x-cmx' => ['cmx'],
    'image/x-icon' => ['ico'],
    'image/x-portable-anymap' => ['pnm'],
    'image/x-portable-bitmap' => ['pbm'],
    'image/x-portable-graymap' => ['pgm'],
    'image/x-portable-pixmap' => ['ppm'],
    'image/x-rgb' => ['rgb'],
    'image/x-xbitmap' => ['xbm'],
    'image/x-xpixmap' => ['xpm'],
    'image/x-xwindowdump' => ['xwd'],
    'message/rfc822' => ['mhtml', 'nws', 'mht'],
    'text/css' => ['css'],
    'text/h323' => ['323'],
    'text/html' => ['html', 'htm', 'dhtml', 'stm'],
    'text/iuls' => ['uls'],
    'text/plain' => ['txt', 'h', 'c', 'bas'],
    'text/richtext' => ['rtx'],
    'text/scriptlet' => ['sct'],
    'text/tab-separated-values' => ['tsv'],
    'text/webviewhtml' => ['htt'],
    'text/x-component' => ['htc'],
    'text/x-setext' => ['etx'],
    'text/x-vcard' => ['vcf'],
    'video/mpeg' => ['mpeg', 'mp2', 'mpg', 'mpa', 'mpe', 'mpv2'],
    'video/quicktime' => ['mov', 'qt'],
    'video/x-la-asf' => ['asf', 'asr', 'asx', 'lsf', 'lsx'],
    'video/x-msvideo' => ['avi'],
    'video/x-sgi-movie' => ['movie'],
    'x-world/x-vrml' => ['vrml', 'wrl', 'flr', 'wrz', 'xaf', 'xof']
  ];
  
  //Initialize.
  public function init()
  {
      
    //Enter a log entry.
    tx('Log')->message($this, 'Mime class initializing.');
    
    //Enter a log entry.
    tx('Log')->message($this, 'Mime class initialized.');
    
  }
  
  public function getMime($type)
  {
    
    $matches = array_search_recusrive($type, $this->mimes);
    
    if($matches === false){
      throw new \exception\NotFound('Could not find a mime for file-type "%s".', $type);
    }
    
    return $matches[0];
    
  }
  
  public function getType($mime)
  {
    
    if(!array_key_exists($mime, $this->mimes)){
      throw new \exception\NotFound('Could not find a type for mime "%s".', $mime);
    }
    
    return $this->mimes[$mime][0];
    
  }
  
  //Return all types associated with the given mime.
  public function getTypes($mime)
  {
      
    if(!array_key_exists($mime, $this->mimes)){
      throw new \exception\NotFound('Could not find the types for mime "%s".', $mime);
    }
    
    return $this->mimes[$mime];
    
  }
  
}
