<?

class AudioMixer
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

    foreach(glob($path."/plugins/AudioMixer_*.php") as $fname)
    {
      require_once($fname);
      preg_match("/AudioMixer_(.+).class.php/", $fname, $matches);
      $effect_name = $matches[1];
      $class_name = "AudioMixer_{$effect_name}";
      $obj = new $class_name();
      self::registerPlugin($effect_name, $obj);
    }
  }
  static function registerPlugin($key, $obj)
  {
    self::$plugins[$key] = $obj;
  }
  

  function __construct()
  {
    $this->clips = array();
    $this->mixdown = null;
  }
  
  function add($clip, $clip_name=null)
  {
    if(is_a($clip, 'AudioClip'))
    {
    } else {
      if(!$clip_name) 
      {
        $parts = pathinfo($clip);
        $clip_name = $parts['filename'];
      }
      $clip = new AudioClip($clip);
    }
    if(isset($this->clips[$clip_name])) dprint("Clip $clip already exists.");
    $this->clips[$clip_name] = $clip;
    return $clip;
  }
  
  function stitch($clip_name)
  {
    if(!isset($this->clips[$clip_name])) dprint("Clip $clip_name does not exist.");
    if(!$this->mixdown)
    {
      $this->mixdown = $this->clips[$clip_name];
    } else {
      $wav = self::$cp->exec("sox -G ? ? <out>", $this->mixdown->fname, $this->clips[$clip_name]->fname);
      $this->mixdown = new AudioClip($wav);
    }
    return $this->mixdown;
  }
  
  function export()
  {
    return $this->mixdown->fname;
  }
  
  function applyEffect($effect_name)
  {
    $args = func_get_args();
    if(isset(self::$plugins[$effect_name]))
    {
      array_shift($args);
      array_unshift($args, $this);
      $wav = call_user_func_array(array(self::$plugins[$effect_name], 'process'), $args);
      $this->mixdown = new AudioClip($wav);
    } else {
      $this->mixdown = call_user_func_array(array($this->mixdown, 'applyEffect'), $args);
    }
    return $this;
  }
}