<?

class VideoMixer_Crossfade
{
  function process($frames)
  {
    $frame_count = count($frames);
    if($frame_count % 2 != 0) dprint("Frame count must be even for crossfade");
    $offset = $frame_count/2;
    VideoMixer::$cp->setTemplate("composite -dissolve !x! ? ? -alpha Set <out>");
    $tween = new SinTween($offset);
    $new_frames = array();
    for($i=0;$i<$offset;$i++)
    {
      $step = $tween->step();
      $new_frames[] = VideoMixer::$cp->add($step, 100-$step, $frames[$i], $frames[$i+$offset]);
    }
    return $new_frames;
  }
}