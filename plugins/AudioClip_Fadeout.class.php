<?

class AudioClip_Fadeout
{
  function process($clip, $samples)
  {
    $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out.!> remix - fade h 0 !s !s rate !", $clip->fname, AUDIO_EXT, $clip->sample_count, $samples, SAMPLE_RATE);
    return $wav;
  }
}