<?

class SingleCommandProcessor
{
  function __construct($cmd_template = null)
  {
    $this->setTemplate($cmd_template);
    $this->commands = array();
    $this->use_cache = true;
  }
  
  function setTemplate($cmd_template)
  {
    $this->template = $cmd_template;
  }
  
  function add()
  {
    $args = func_get_args();
    array_unshift($args, $this->template);
    $cmd = call_user_func_array('interpolate', $args);
    $md5 = md5(join(':',$args));
    $dst_fname = "tmp/frames/$md5.".EXT;
    if(!$this->use_cache || !file_exists($dst_fname))
    {
      $cmd = str_replace("<out>", escapeshellarg($dst_fname), $cmd);
      $this->commands[] = $cmd;
    }
    return $dst_fname;
  }
  
  function process()
  {
    foreach($this->commands as $cmd)
    {
      cmd($cmd);
    }
  }
}
