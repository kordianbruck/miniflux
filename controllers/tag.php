<?php

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Template;

// Submit edit feed form
Router\post_action('tag-create', function() {
    $values = Request\values();
    if(!isset($values['title']) || empty($values['title'])){
        //TODO
        return false;
    }
    
    $tags = Model\Tag\create($values['title']);
    if(empty($tags)){
        //TODO
        return false;
    }
    
    Response\html(Template\load('tag_single', array(
        'tags' => $tags
    )));
});
