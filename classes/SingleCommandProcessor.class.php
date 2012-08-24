<?

class SingleCommandProcessor
{
  function __construct($path)
  {
    ensure_writable_folder($path);
    $this->path = $path;
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
    ensure_writable_folder($this->path."/intermediate");
    $dst_fname = $this->path."/intermediate/$md5";
    $should_run = true;
    $cmd = preg_replace_callback("/<(outdir)(.*)>/", function($matches) use (&$dst_fname, &$should_run) {
      $should_run = !file_exists($dst_fname);
      ensure_writable_folder($dst_fname);
      return escapeshellarg($dst_fname . $matches[2]);
    }, $cmd);
    $cmd = preg_replace_callback("/<(out)(.*)>/", function($matches) use (&$dst_fname, &$should_run) {
      $dst_fname .= $matches[2];
      $should_run = !file_exists($dst_fname);
      return escapeshellarg($dst_fname);
    }, $cmd);
    if(!$this->use_cache || $should_run)
    {
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
