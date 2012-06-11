<?

class Video
{
  static $plugins = array();
  static function registerPlugin($key, $obj)
  {
    self::$plugins[$key] = $obj;
  }
  
  function __construct($fname, $marker_name=null)
  {
    $this->stitch_limit = 500;
    $this->frames = array();
    $this->frame_count = 0;
    $this->markers = array();
    $this->assets = array();
    $this->md5 = $this->key($fname);
    $this->fpath = "tmp/master.{$this->md5}";
    if(file_exists($this->fpath))
    {
      cmd("rm -rf ?", $this->fpath);
    }
    mkdir($this->fpath);
    $this->add($fname, $marker_name);
    
  }
  
  function key($fname)
  {
    return md5(EXT.md5_file($fname));
  }
  
  function add($fname, $asset_name = null)
  {
    $md5 = $this->key($fname);
    $unpacked_fname = "tmp/$md5";
    if(!file_exists($unpacked_fname))
    {
      mkdir($unpacked_fname);
      cmd("ffmpeg -i ? -b 30 $unpacked_fname/%010d.!", $fname, EXT);
    }
    if($asset_name==null)
    {
      $parts = pathinfo($fname);
      $asset_name = $parts['filename'];
    }
    if(isset( $this->assets[$asset_name])) dprint("Duplicate asset name $fname");
    $this->assets[$asset_name] = $unpacked_fname;
    return $md5;
  }
  
  function stitch($asset_name, $marker=null)
  {
    if(!$marker) $marker = $asset_name;
    if(isset($this->markers[$marker])) dprint("Marker already in use $marker");
    $fpath = $this->assets[$asset_name];
    $c = count(glob($fpath."/*.".EXT));
    $this->markers[$marker] = count($this->frames);
    if($this->stitch_limit) $c = min($c, $this->stitch_limit);
    for($i=1;$i<=$c;$i++)
    {
      $this->frames[] = sprintf("%s/%010d.%s", $fpath, $i,EXT);
    }
  }
  
  function apply($frame_idx, $cmd)
  {
    $args = func_get_args();
    $frame_idx = array_shift($args);
    $cmd = array_shift($args);
    $src_frame_fname = $this->frames[$frame_idx];
    $cmd = str_replace("<in>", "\"{$src_frame_fname}\"", $cmd);
    $key = $args;
    $key[] = $cmd;
    $key[] = EXT;
    $key = md5(join(':',$key));
    $dst_frame_fname = "tmp/frames/$key.".EXT;
    if(!file_exists($dst_frame_fname))
    {
      $cmd = str_replace("<out>", "\"{$dst_frame_fname}\"", $cmd);
      array_unshift($args, $cmd);
      call_user_func_array('cmd', $args);
    }
    $this->frames[$frame_idx] = $dst_frame_fname;
  }
  
  function applyEffect()
  {
    $args = func_get_args();
    $effect_name = array_shift($args);
    if(!isset(self::$plugins[$effect_name])) dprint("Effect $effect_name not found.");
    $start_marker = array_shift($args);
    $stop_marker = array_shift($args);
    if(is_numeric($start_marker))
    {
      $start_frame = $start_marker;
    } else {
      if(is_a($start_marker, 'Marker'))
      {
        $start_frame = $this->markers[$start_marker->marker] + $start_marker->offset;
      } else {
        $start_frame = $this->markers[$start_marker];
      }
    }
    if(is_numeric($stop_marker))
    {
      $stop_frame = $start_frame+$stop_marker-1;
    } else {
      if(is_a($stop_marker, 'Marker'))
      {
        $stop_frame = $this->markers[$stop_marker->marker] + $stop_marker->offset - 1;
      } else {
        $stop_frame = $this->markers[$stop_marker] - 1;
      }
    }
    $frame_count = $stop_frame - $start_frame + 1;
    $frames = array_slice($this->frames, $start_frame, $frame_count);
    array_unshift($args, $frames);
    $cp = new SingleCommandProcessor();
    array_unshift($args, $cp);
    $new_frames = call_user_func_array(array(self::$plugins[$effect_name], 'process'), $args);
    $cp->process();
    $frame_diff = count($new_frames)-$frame_count;
    foreach($this->markers as $k=>$v)
    {
      if($v>$start_frame)
      {
        $this->markers[$k] = $v+$frame_diff;
      }
    }
    array_splice($this->frames, $start_frame, $frame_count, $new_frames);
  }
  
  function export()
  {
    $start = microtime(true);
    for($i=0;$i<count($this->frames);$i++)
    {
      $src_fname = $this->frames[$i];
      $dst_fname = sprintf("%s/%010d.%s", $this->fpath, $i+1, EXT);
      copy($src_fname, $dst_fname);
    }
    $src_fname = sprintf("%s/%%010d.%s", $this->fpath, EXT);
    $dst_fname = sprintf("%s.mp4", $this->fpath);
    cmd("ffmpeg -y -i ? -r 30 -b:v 400k -vcodec libx264 ?", $src_fname, $dst_fname);
    dprint(count($this->frames));
    return $dst_fname;
  }
}