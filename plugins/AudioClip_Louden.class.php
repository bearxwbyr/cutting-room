<?

class AudioClip_Louden
{
  function process($clip)
  {
    $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out.!> remix - gain -n rate !", $clip->fname, AUDIO_EXT, SAMPLE_RATE);
    return $wav;
  }
}