<?

class VideoMixer_Fadeout
{
  function process($frames)
  {
    $frame_count = count($frames);
    $tween = new SinTween($frame_count);
    $cmd_template = "convert ? -fill black -colorize !% <out.!>";
    VideoMixer::$cp->setTemplate($cmd_template);
    for($i=$frame_count-1;$i>0;$i--)
    {
      $frames[$i] = VideoMixer::$cp->add($frames[$i], $tween->step(), VIDEO_EXT);
    }
    return $frames;
  }
}