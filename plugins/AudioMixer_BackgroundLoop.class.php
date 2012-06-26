<?

class AudioMixer_BackgroundLoop
{
  function process($mixer, $vol, $pre, $loop, $post, $options = array())
  {
    $m = new AudioMixer();
    $m->add($mixer->clips[$pre], 'pre');
    $m->add($mixer->clips[$loop], 'loop');
    $m->add($mixer->clips[$post], 'post');
    
    $m->stitch('pre');
    $extra_samples = 0;
    if(isset($options['extra_samples'])) $extra_samples = $options['extra_samples'];
    $samples_needed = $mixer->mixdown->sample_count - $mixer->clips[$pre]->sample_count - $mixer->clips[$post]->sample_count + $extra_samples;
    $i = $samples_needed;
    dprint($samples_needed,false);
    while($i>0)
    {
      $m->stitch('loop');
      $i -= $mixer->clips[$loop]->sample_count; 
      dprint($i,false);
    }
    $m->stitch('post');
    $m->applyEffect('Volume', $vol);
    if(isset($options['ending']))
    {
      $m->applyEffect('Trim', $samples_needed);
      $m->applyEffect('Fadeout', 3*48000);
    }
    $wav = AudioMixer::$cp->exec("sox -G  -m ? ? -b 24 <out> remix - gain -n    rate 48k", $mixer->mixdown->fname, $m->mixdown->fname);
    return $wav;
  }
}