#!/bin/sh
<?

/*

track  [fadein][pre][xfade][intro][xfade][main][xfade][altro][xfade][post][fadeout]
track                      [in-au]       [mnau]       [al-au]       [ptau]
track                      [text............................]
track                      [pad..]       [pad.....................................]
track                                    [cntr]



*/
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');

function __autoload($class_name)
{
  $fname = dirname(__FILE__)."/classes/{$class_name}.class.php";
  if(!file_exists($fname)) dprint("Class not found: $class_name");
  require_once($fname);
}
spl_autoload_register('__autoload');

foreach(glob("lib/*.php") as $lib_fname)
{
  require($lib_fname);
}

AudioMixer::init(dirname(__FILE__),dirname(__FILE__)."/tmp/audio");
AudioClip::init(dirname(__FILE__),dirname(__FILE__)."/tmp/audio");
VideoMixer::init(dirname(__FILE__),dirname(__FILE__)."/tmp/video");


define('EXT', 'png');

AudioMixer::$tmp_path = dirname(__FILE__)."/tmp/audio";

$a = new AudioMixer();
$a->add(dirname(__FILE__)."/".$argv[1], 'main')->applyEffect('Louden');
$a->add(dirname(__FILE__).'/assets/altro.m4v')->applyEffect('Louden');
$a->add(dirname(__FILE__).'/assets/pad_intro.wav');
$a->add(dirname(__FILE__).'/assets/pad_loop.wav');
$a->add(dirname(__FILE__).'/assets/pad_altro.wav');
$a->stitch('main');
$a->stitch('altro');
$a->applyEffect('BackgroundLoop', 0.10, 'pad_intro', 'pad_loop', 'pad_altro', 2*48000);
dprint($a->export());

$pad = new Audio( 'intro');
$pad->add();
$pad->add();
$pad->stitch('pad_intro');
$samples = $a->sample_count - $pad->clips['pad_intro']->sample_count - $pad->clips['pad_altro']->sample_count;
while($samples>0)
{
  $pad->stitch('pad_loop');
  $samples -= $pad->clips['pad_loop']->sample_count;
}
$pad->stitch('pad_altro');
$pad->applyEffect('fadeout', 'pad_altro', 'end');
$a->applyEffect('mix', 'main');


$a->stitch('main');
$a->applyEffect('loop', 'main', 'end');

Video::$tmp_path = dirname(__FILE__)."/tmp/video";
Video::registerPlugin('fadein', new FadeinEffect());
Video::registerPlugin('animation', new AnimationEffect());
Video::registerPlugin('byline', new BylineEffect());
Video::registerPlugin('crossfade', new CrossfadeEffect());
Video::$cp = new ParallelCommandProcessor();

$v = new Video(dirname(__FILE__)."/".$argv[1], 'main');
$v->add(dirname(__FILE__).'/assets/preroll.m4v');
$v->add(dirname(__FILE__).'/assets/intro.m4v');
$v->add(dirname(__FILE__).'/assets/altro.m4v');
$v->add(dirname(__FILE__).'/assets/postroll.m4v');

$v->stitch('preroll');
$v->stitch('intro');
$v->stitch('main');
$v->stitch('altro');
$v->stitch('postroll');
$v->applyEffect('animation', 'main', 'altro', dirname(__FILE__).'/assets/countdown_main.mp4', dirname(__FILE__).'/assets/countdown_pulse.mp4');
dprint('done');
$v->applyEffect('byline', 'intro', 'postroll', '90 Second Lessons', 'Fun, expert instruction. Straight to the point.', '$19.99 monthly. Sign up at http://coaching.benallfree.com.');
$v->applyEffect('fadein', 'preroll', 30);
$v->applyEffect('crossfade', new Marker('intro', -30), new Marker('intro', 30));
$v->applyEffect('crossfade', new Marker('main', -30), new Marker('main', 30));
$v->applyEffect('crossfade', new Marker('altro',-30), new Marker('altro', 30));
$v->applyEffect('crossfade', new Marker('postroll', -30), new Marker('postroll', 30));
dprint($v->export());

?>

# ------------------------
# INIT
# ------------------------
rm -rf clips/*
rm -rf build/*

# Convert all videos to MPG format for concatenation
ffmpeg  -i countdown_main.mp4 -sameq -s 100x75 -y -map 0:v -r 30 clips/countdown_main.mpg
ffmpeg  -i countdown_pulse.mp4 -sameq  -s 100x75 -y -map 0:v -r 30 clips/countdown_pulse.mpg
<?
$clip = new Clip('preroll.m4v');
?>
ffmpeg  -i preroll.m4v -sameq -y -map 0:v -r 30 -vf "fade=in:0:30, fade=out:<?=$clip->streams[0]->frame_count - 10?>:10" clips/preroll.mpg
ffmpeg  -i postroll.m4v -sameq -y -map 0:v -r 30 -vf "fade=in:0:10" clips/postroll.mpg
<?
$clip = new Clip('intro.m4v');
?>
ffmpeg -i intro.m4v -sameq -y -map 0:0 -r 30 -vf "fade=in:0:10, fade=out:<?=$clip->streams[0]->frame_count - 10?>:10"  clips/intro.mpg
ffmpeg -i altro.m4v -sameq -y -map 0:0 -r 30 -vf "fade=in:0:10"  clips/altro.mpg

# Extract audio streams
ffmpeg -i intro.m4v -map 0:a -y  clips/intro_tmp.wav
sox -G clips/intro_tmp.wav  -b 24 clips/intro.wav remix 1-2 gain -n loudness rate 48k

# Convert to mono
sox -G pad_intro.wav -c 1 -b 24 clips/pad_intro.wav remix 1-2 vol 0.05  rate 48k
sox -G pad_loop.wav -c 1  -b 24 clips/pad_loop.wav remix 1-2 vol 0.05  rate 48k
sox -G pad_altro.wav -c 1  -b 24 clips/pad_altro.wav remix 1-2 vol 0.05  rate 48k


# -------------------------
# VIDEO
# -------------------------
#preroll
cat clips/preroll.mpg clips/intro.mpg > build/preroll_tmp.mpg
ffmpeg -i build/preroll_tmp.mpg -sameq -y build/preroll.mpg

# main
ffmpeg -i "<?=$argv[1]?>" -sameq -y -map 0:v -r 30 build/main_tmp.mpg
ffmpeg -i "<?=$argv[1]?>" -y -map 0:a build/main_tmp.wav

<? 
$clip = new Clip('build/main_tmp.mpg'); 
?>
ffmpeg -i build/main_tmp.mpg -sameq -y -vf 'movie=clips/countdown_main.mpg [movie]; [in] [movie] overlay=W-w-10:10, fade=in:0:10, fade=out:<?=$clip->streams[0]->frame_count-10?>:10' build/main_temp2.mpg
cat build/main_temp2.mpg clips/altro.mpg clips/postroll.mpg > build/main_tmp3.mpg
ffmpeg -i build/main_tmp3.mpg -sameq -y build/main.mpg

# final video
cat build/preroll.mpg build/main.mpg > build/final.mpg

# -------------------------
# AUDIO
# -------------------------

# ------- PREROLL ---------
# silent lead-in the same size/length as clips/preroll.mpg
<?
$preroll = new Clip('clips/preroll.mpg');
?>
sox -G -n -c 1 -b 24 build/preroll_silence.wav trim 0.0 <?=seconds_to_time($clip->duration)?> rate 48k

# construct preroll pad = intro pad + loop + altro pad
# intro length:  ffmpeg -i clips/intro.mpg = 01:25.36
# pad intro length = 3.0
# pad loop length = 1.0
# pad altro = 1:40.11
# NO LOOP NECESSARY
# total length required = 3.96 + 1:25.36 = 3.96 + 85.36 = 89.32
<?
$intro = new Clip('clips/intro.mpg');
$total_intro_pad_length = $preroll->duration + $intro->duration;
?>
sox -G  build/preroll_silence.wav clips/pad_intro.wav clips/pad_altro.wav -b 24 build/preroll_pad.wav trim 0 <?=seconds_to_time($total_intro_pad_length)?> fade l 0 0 1  rate 48k

# add silent pad to intro.wav and pad_intro.wav
sox -G  build/preroll_silence.wav clips/intro.wav -b 24 build/preroll_tmp.wav    rate 48k

# mix the intro and pad together
sox -G  -m build/preroll_tmp.wav build/preroll_pad.wav -b 24 build/preroll.wav gain -n    rate 48k

# ------- MAIN --------------------

# construct main pad = intro pad + loop + altro pad
# main length:  ffmpeg -i build/main.mpg = 00:01:46.63
# pad intro length = 3.0
# pad loop length = 1.0
# pad altro = 1:40.11
# LOOP NECESSARY: 00:01:46.63 - 1:40.11 = 106.63 - 100.11 = 6.52 = 7 LOOPS
<?
$main = new Clip('build/main.mpg');
$pad_intro = new Clip('clips/pad_altro.wav');
$pad_altro = new Clip('clips/pad_altro.wav');
$loop_count = $main->duration; 
?>
sox  clips/pad_loop.wav -b 24 build/pad_loop.wav repeat 7    rate 48k
sox -G  clips/pad_intro.wav build/pad_loop.wav clips/pad_altro.wav build/main_pad.wav    rate 48k

# mix the main and pad together
sox -G  build/main_tmp.wav -b 24 build/main_tmp2.wav remix 1-2 gain -n loudness    rate 48k
sox -G  -m build/main_tmp2.wav build/main_pad.wav -b 24 build/main.wav gain -n    rate 48k


ffmpeg  -i build/main_tmp.mpg  -i build/main_tmp.wav -map 0:v -r 30 -map 1:a -y -b:v 400k -b:a 192k -f mp4 -vcodec libx264 -strict experimental -acodec aac build/main_test.mp4

# ------- FINAL AUDIO MIX ---------

# mix all audio
sox -G  build/preroll.wav build/main.wav -b 24 build/final.wav gain -n    rate 48k

# -------------------------
# Final Mixdown
# -------------------------


ffmpeg  -i build/final.mpg  -i build/final.wav -map 0:v -r 30 -map 1:a -y -b:v 400k -b:a 192k -f mp4 -vcodec libx264 -strict experimental -acodec aac build/final.mp4
