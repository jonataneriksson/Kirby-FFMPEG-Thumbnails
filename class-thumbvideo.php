<?php

class ThumbVideo extends Thumb {

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Construct function */
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  public function __construct($source, $params = array()) {

    //
    $this->source = $this->result = is_a($source, 'Media') ? $source : new Media($source);

    //Setting up defaults. This plugin is only for videos so we can use FFMPEG anywhere.
    static::$defaults['still'] = true;
    static::$defaults['silent'] = true;

    $this->options = array_merge(static::$defaults, $this->params($params));
    $this->destination = $this->destination();

    // don't create the thumbnail if it exists
    if(!$this->isThere()) {

      // try to create the thumb folder if it is not there yet
      if(!file_exists(dirname($this->destination->root))){
        dir::make(dirname($this->destination->root));
      }

      // create the thumbnail
      $this->create();

      // check if creating the thumbnail failed
      if(!file_exists($this->destination->root)) return;
    }

    //Let's check the JPEG path too
    $jpgroot = pathinfo($this->destination->root)['dirname'].DS.pathinfo($this->destination->root)['filename'].'.jpg';
    $jpgurl = pathinfo($this->destination->url)['dirname'].DS.pathinfo($this->destination->url)['filename'].'.jpg';

    //If 'still' option is on, return the jpg
    if($this->options['still'] == true && file_exists($jpgroot)){
      $result = new Media($jpgroot, $jpgurl);
      $this->result = $result;
      return;
    }

    // create the result object
    $result = new Media($this->destination->root, $this->destination->url);
    $this->result = $result;
  }

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Check if the file is there*/
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  public function isThere() {

    if($this->options['overwrite'] === true) return false;

    //If were getting a still image from the video let's look for jpg.
    $jpg = pathinfo($this->destination->root)['dirname'].DS.pathinfo($this->destination->root)['filename'].'.jpg';
    $imageisthere = file_exists($jpg);

    if($this->options['still'] == true && file_exists($jpg) && f::modified($jpg) >= $this->source->modified()) return true;

    // if the thumb already exists and the source hasn't been updated we don't need to generate a new thumbnail
    if(file_exists($this->destination->root) && f::modified($this->destination->root) >= $this->source->modified()) return true;

    return false;
  }

  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  /* !Builds a hash for all relevant settings */
  /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

  public function settingsIdentifier() {
    // build the settings string
    return implode('-', array(
      ($this->options['width']) ? $this->options['width']   : 0,
      ($this->options['height']) ? $this->options['height']  : 0,
       $this->options['still'],
       $this->options['silent'],
       $this->options['clip']
    ));
  }

}
