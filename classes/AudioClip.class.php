<?

class AudioClip
{
  static $plugins = array();
  static $tmp_path = null;
  static $cp = null;
  
  static function init($path, $tmp_path)
  {
    self::$tmp_path = $tmp_path;
    if(!self::$cp)
    {
      self::$cp = new SingleCommandProcessor(self::$tmp_path, 'wav');
    }

    foreach(glob($path."/plugins/AudioClip_*.php") as $fname)
    {
      require_once($fname);
      preg_match("/AudioClip_(.+).class.php/", $fname, $matches);
      $effect_name = $matches[1];
      $class_name = "AudioClip_{$effect_name}";
      $obj = new $class_name();
      self::registerPlugin($effect_name, $obj);
    }
  }
  static function registerPlugin($key, $obj)
  {
    self::$plugins[$key] = $obj;
  }
  

  function __construct($fname)
  {
    $this->original_fname = $fname;
    $this->fname = $this->convert($fname);
    $this->getInfo();
  }
  
  function convert($fname)
  {
    if(is_object($fname)) dprint('wtf');
    $parts = pathinfo($fname);
    $ext = strtolower($parts['extension']);
    switch($ext)
    {
      case 'mp4':
      case 'm4v':
      case 'mov':
        $wav = AudioMixer::$cp->exec("ffmpeg -i ? -map 0:a -y <out.!>", $fname, AUDIO_EXT);
        break;
      case 'wav':
        $wav = $fname;
        break;
      default:
        dprint("File extension not recognized $fname");    
    }
    if(substr($wav, 0, strlen(self::$tmp_path))!=self::$tmp_path)
    {
      $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out.!> remix - rate !", $wav, AUDIO_EXT, SAMPLE_RATE);
    }
    return $wav; 
  }
  
  function getInfo($fname = null)
  {
    if(!$fname) $fname = $this->fname;
    $out = cmd("soxi ?", $fname);
    $out = join("\n",$out);
    preg_match("/(\d+) samples/", $out, $matches);
    $this->sample_count = $matches[1];
  }
  
  function applyEffect($effect_name)
  {
    if(!isset(self::$plugins[$effect_name])) dprint("Effect $effect_name not found.");
    $args = func_get_args();
    array_shift($args);
    array_unshift($args, $this);
    $wav = call_user_func_array(array(self::$plugins[$effect_name], 'process'), $args);
    $this->fname = $wav;
    $this->getInfo();
    return $this;
  }
}  
  
