<?

class AudioClip_Volume
{
  function process($clip, $vol)
  {
    $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out> remix - vol ? rate 48k", $clip->fname, $vol);
    return $wav;
  }
}