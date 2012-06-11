<?




function dprint($s, $should_exit = true)
{
  var_dump($s);
  if($should_exit)
  {
    foreach(debug_backtrace() as $t)
    {
      if(!isset($t['file'])) $t['file'] = '';
      if(!isset($t['line'])) $t['line'] = '';
      echo("{$t['file']}:{$t['line']}\n");
    }
    die;
  }
}
