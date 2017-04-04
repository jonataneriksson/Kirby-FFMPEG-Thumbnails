<?php

/**
* Modified ImageMagick Driver
*/
thumb::$drivers['ffmpeg'] = function($thumb) {

  $command = array();

  $ffmpeg = isset($thumb->options['ffmpeg']) ? $thumb->options['ffmpeg'] : 'ffmpeg';

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Run ffmpeg without interruptions */
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  if($thumb->source->type() == 'video'):
    $command[] = $ffmpeg.' -y';
  endif;

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Images and clips from the middle */
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  if(isset($thumb->options['clip']) && isset($thumb->options['clipstart'])) {
    $duration = $thumb->options['duration'];
    $clipstart = $thumb->options['clipstart'];
    $playroom = $duration - $clipstart;
    $cliptime = ($playroom > 8) ? 8 : $playroom;
    $command[] = '-t '.$cliptime;
    $command[] = '-ss '.$thumb->options['clipstart'];
  } else if(isset($thumb->options['clip'])) {
    if($thumb->options['clip']):
      $duration = $thumb->options['duration'];
      $start = round($duration/2);
      $command[] = '-ss '.$start;
      $command[] = '-t 8';
    endif;
  } else {
    $command[] = '-ss 0';
  }

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Source after seeking */
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  if($thumb->source->type() == 'video'):
    $command[] = '-i "' . $thumb->source->root() . '"';
  endif;

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Resizing */
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  if($thumb->options['height'] && !$thumb->options['width'] && $thumb->source->dimensions()->height > $thumb->options['height']) {
    $command[] = '-vf scale="-2:'.$thumb->options['height'].'"';
  }

  if($thumb->options['width'] && !$thumb->options['height'] && $thumb->source->dimensions()->width > $thumb->options['width']) {
    $command[] = '-vf scale="'.$thumb->options['width'].':-2"';
  }

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Quality */
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  if($thumb->options['silent']) {
    $command[] = '-an';
  }

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Quality */
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  if($thumb->options['quality'] && !$thumb->options['still']) {
    //$invertedratio = (100-$thumb->options['quality'])/100;
    //$crfratio = intval(51 * $invertedratio);
    $crfratio = 21;
    $command[] = '-c:v libx264';
    $command[] = '-tune grain';
    $command[] = '-preset fast';
    $command[] = '-crf '.$crfratio;
  }

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Output (2>&1 for independent)*/
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  if($thumb->options['still']) {
    $command[] = '-frames:v 1';
    $command[] = '-f mjpeg';
    $root = pathinfo($thumb->destination->root);
    $command[] = '"' .$root['dirname'].DS.$root['filename'].'.jpg"';
  } else {
    $command[] = '"' . $thumb->destination->root . '"';
  }

  $command[] = '2>&1';

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Exec */
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  $execstring = implode(' ', $command);

  //print_r($execstring);

  exec($execstring);

  //echo(shell_exec($execstring));

};
