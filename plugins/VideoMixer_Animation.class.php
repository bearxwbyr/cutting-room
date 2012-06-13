<?

class VideoMixer_Animation
{
  function process($frames, $lead_in, $loop)
  {
    $overlay = new Video($lead_in, 'lead');
    $overlay->add($loop, 'loop');
    $overlay->stitch('lead');
    
    
    cmd("rm -rf tmp/link");
    mkdir("tmp/link");
    
    VideoMixer::$cp->setTemplate("composite  -dissolve 80x100 \( -gravity NorthEast -geometry 180x90+10+10 -bordercolor white -border 1x1 ? \) ? <out>");
    for($i=0;$i<count($frames);$i++)
    {
      $src_fname = $frames[$i];
      if(!isset($overlay->frames[$i]))
      {
        $overlay->stitch('loop', "loop_{$i}");
      }
      $overlay_fname = $overlay->frames[$i];
      $frames[$i] = VideoMixer::$cp->add($overlay_fname, $src_fname);
    }
    return $frames;
  }
    
}