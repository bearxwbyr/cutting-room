<?

class AudioClip_Trim
{
  function process($clip, $samples)
  {
    $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out.!> remix - trim 0 !s rate !", $clip->fname, AUDIO_EXT, $samples, SAMPLE_RATE);
    return $wav;
  }
}