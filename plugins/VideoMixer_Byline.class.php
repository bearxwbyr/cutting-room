<?

class VideoMixer_Byline
{
  function process($frames, $line1, $line2=null, $line3=null)
  {
    $w = 640;
    $bw = $w+2;
    $h = 480;
    $bh = 80;
    $bx = -1;
    $by = $h - $bh - 10;
    $filters = array(
      interpolate('-fill ? -stroke black -draw ?', "#181718", "fill-opacity 0.9    rectangle !,! !,!", $bx, $by, $bx+$bw, $by+$bh),
      interpolate("-pointsize 24 -font PontanoSans -fill white -stroke 'rgba(200,200,200,0.8)' -weight bold -gravity NorthWest -annotate +!+! ?", $bx+15, $by+5, $line1),
      interpolate("-pointsize 18 -font PontanoSans -fill white -stroke 'rgba(200,200,200,0.8)' -weight normal -gravity NorthWest -annotate +!+! ?", $bx+15, $by+5+27, $line2),
      interpolate("-pointsize 18 -font PontanoSans -fill white -stroke 'rgba(200,200,200,0.8)' -weight normal -gravity NorthWest -annotate +!+! ?", $bx+15, $by+5+27+23, $line3),
    );
    $filters = join(' ', $filters);
    $cmd_template = "convert ? ! <out>"; 
    VideoMixer::$cp->setTemplate($cmd_template);
    for($i=0;$i<count($frames);$i++)
    {
      $frames[$i] = VideoMixer::$cp->add($frames[$i], $filters);
    }
    return $frames;
  }
}