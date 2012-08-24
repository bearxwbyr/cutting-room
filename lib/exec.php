<?

function interpolate()
{
  $args = func_get_args();
  $s = array_shift($args);
  foreach($args as $arg)
  {
    $s = preg_replace_callback("/([!?])/", function($matches) use ($arg) {
      if(count($matches)<=1) return;
      switch($matches[1])
      {
        case '?':
          return escapeshellarg($arg);
          break;
        case '!':
          return $arg;
          break;
        default:
          dprint("Unknown type $type in interpolate");
      }
      
    }, $s, 1);
  }
  return $s;
}

function cmd($cmd)
{
  $args = func_get_args();
  $s = call_user_func_array('interpolate', $args);
  return _cmd($s);
}

function _cmd($s)
{
  echo($s."\n");
  exec($s . " 2>&1",$output, $result);
  if($result!=0)
  {
    dprint("Error: $result",false);
    dprint($s,false);
    dprint($output);
  }
  return $output;
}

