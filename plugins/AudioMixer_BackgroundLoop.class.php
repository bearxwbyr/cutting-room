<?

class AudioMixer_BackgroundLoop
{
  function process($mixer, $vol, $pre, $loop, $post, $extra_samples=0)
  {
    $m = new AudioMixer();
    $m->add($mixer->clips[$pre], 'pre');
    $m->add($mixer->clips[$loop], 'loop');
    $m->add($mixer->clips[$post], 'post');
    
    $m->stitch('pre');
    $samples_needed = $mixer->mixdown->sample_count - $mixer->clips[$pre]->sample_count - $mixer->clips[$post]->sample_count + $extra_samples;
    dprint($samples_needed,false);
    while($samples_needed>0)
    {
      $m->stitch('loop');
      $samples_needed -= $mixer->clips[$loop]->sample_count; 
      dprint($samples_needed,false);
    }
    $m->stitch('post');
    $m->applyEffect('Volume', $vol);
    $wav = AudioMixer::$cp->exec("sox -G  -m ? ? -b 24 <out> remix - gain -n    rate 48k", $mixer->mixdown->fname, $m->mixdown->fname);
    return $wav;
  }
}