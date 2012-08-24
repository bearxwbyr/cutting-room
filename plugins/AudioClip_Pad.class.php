<?

class AudioClip_Pad
{
  function process($clip, $pad_start, $pad_end)
  {
    $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out.!> remix - pad !s !s rate !", $clip->fname, AUDIO_EXT, $pad_start, $pad_end, SAMPLE_RATE);
    return $wav;
  }
}