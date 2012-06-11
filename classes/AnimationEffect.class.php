<?

class AnimationEffect
{
  function process($cp, $frames, $lead_in, $loop)
  {
    $overlay = new Video($lead_in, 'lead');
    $overlay->add($loop, 'loop');
    $overlay->stitch('lead');
    
    $cp->setTemplate("composite -gravity NorthEast -geometry +10+10 ? ? <out>");
    for($i=0;$i<count($frames);$i++)
    {
      $src_fname = $frames[$i];
      $overlay_fname = $overlay->frames[$i];
      $frames[$i] = $cp->add($overlay_fname, $src_fname);
    }
    return $frames;
  }
    
}