<?

class AudioClip_Trim
{
  function process($clip, $samples)
  {
    $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out> remix - trim 0 !s rate 48k", $clip->fname, $samples);
    return $wav;
  }
}