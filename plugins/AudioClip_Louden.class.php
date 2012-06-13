<?

class AudioClip_Louden
{
  function process($clip)
  {
    $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out> remix - gain -n loudness rate 48k", $clip->fname);
    return $wav;
  }
}