<?
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');

function __autoload($class_name)
{
  $fname = dirname(__FILE__)."/classes/{$class_name}.class.php";
  if(!file_exists($fname)) dprint("Class not found: $class_name");
  require_once($fname);
}
spl_autoload_register('__autoload');

foreach(glob("lib/*.php") as $lib_fname)
{
  require($lib_fname);
}

define('AUDIO_EXT', 'wav');
define('VIDEO_EXT', 'jpg');
define('SAMPLE_RATE', 44100);
define('FRAME_RATE', 20);

AudioMixer::init(dirname(__FILE__),dirname(__FILE__)."/tmp/audio");
AudioClip::init(dirname(__FILE__),dirname(__FILE__)."/tmp/audio");
VideoMixer::init(dirname(__FILE__),dirname(__FILE__)."/tmp/video");

