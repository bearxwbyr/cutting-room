<?

class VideoMixer_Fadein
{
  function process($frames)
  {
    $frame_count = count($frames);
    $tween = new SinTween($frame_count);
    $cmd_template = "convert ? -fill black -colorize !% <out>";
    VideoMixer::$cp->setTemplate($cmd_template);
    for($i=0;$i<$frame_count;$i++)
    {
      $frames[$i] = VideoMixer::$cp->add($frames[$i], $tween->step());
    }
    return $frames;
  }
}