<?

class SingleCommandProcessor
{
  function __construct($path, $ext)
  {
    ensure_writable_folder($path);
    $this->path = $path;
    $this->ext = $ext;
    $this->commands = array();
    $this->use_cache = true;
  }
  
  public function setTemplate($cmd_template)
  {
    $this->template = $cmd_template;
    $this->commands = array();
  }
  
  function exec()
  {
    $args = func_get_args();
    $this->setTemplate(array_shift($args));
    $out_fname = call_user_func_array(array($this,'add'), $args);
    $this->process();
    return $out_fname;
  }
  
  public function add()
  {
    $args = func_get_args();
    array_unshift($args, $this->template);
    $cmd = call_user_func_array('interpolate', $args);
    $md5 = md5(join(':',$args));
    $dst_fname = $this->path."/$md5.".$this->ext;
    if(!$this->use_cache || !file_exists($dst_fname))
    {
      $cmd = str_replace("<out>", escapeshellarg($dst_fname), $cmd);
      $this->commands[] = $cmd;
    }
    return $dst_fname;
  }
  
  public function process()
  {
    $start = microtime(true);
    foreach($this->commands as $cmd)
    {
      cmd($cmd);
    }
    $end = microtime(true);
    dprint(sprintf("Processed %d commands in %f seconds.", count($this->commands), $end-$start), false);
  }
}
