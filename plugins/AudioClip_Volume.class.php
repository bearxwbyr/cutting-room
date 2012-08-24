<?

class AudioClip_Volume
{
  function process($clip, $vol)
  {
    $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out.!> remix - vol ? rate !", $clip->fname, AUDIO_EXT, $vol, SAMPLE_RATE);
    return $wav;
  }
}