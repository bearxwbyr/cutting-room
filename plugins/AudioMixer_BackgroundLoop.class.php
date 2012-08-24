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
    while($i>0)
    {
      $m->stitch('loop');
      $i -= $mixer->clips[$loop]->sample_count; 
    }
    $m->stitch('post');
    $m->applyEffect('Volume', $vol);
    if(isset($options['ending']))
    {
      $m->applyEffect('Trim', $samples_needed);
      $m->applyEffect('Fadeout', 3*SAMPLE_RATE);
    }
    $wav = AudioMixer::$cp->exec("sox -G  -m ? ? -b 24 <out.!> remix - gain -n    rate !", $mixer->mixdown->fname, $m->mixdown->fname, AUDIO_EXT, SAMPLE_RATE);
    return $wav;
  }
}