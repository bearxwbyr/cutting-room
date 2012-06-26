<?

class AudioClip_Fadeout
{
  function process($clip, $samples)
  {
    $wav = AudioMixer::$cp->exec("sox -G ? -b 24 <out> remix - fade h 0 !s !s rate 48k", $clip->fname, $clip->sample_count, $samples);
    return $wav;
  }
}