<?php

namespace Model\Tag;

use PicoDb\Database;

//Get all tags which exsist and are linked to this feed
function get_feed_tags($feed)
{
    return Database::get('db')
            ->table('tags')
            ->join('feeds2tags', 'tag', 'id')
            ->beginOr()
            ->eq('feed',$feed)
            ->isnull('feed')
            ->closeOr()
            ->findAll();
}

// Update feed tagging information
function update_feed_tags(array $values)
{   
    //First remove all tags 
    remove_feed($values['id']);
    
    //Then re add those which were still selected
    foreach ($values['tag'] as $e){
        if(!empty($e)){
            Database::get('db')->table('feeds2tags')->insert(array('feed'=>$values['id'], 'tag'=>$e));
        }
    }
    
    return true;
}

// Add a new tag 
function create($title)
{
    Database::get('db')->table('tags')->insert(array('title'=>$title));
    return Database::get('db')->table('tags')->eq('title', $title)->findAll();
}

// Remove one tag
function remove($tag)
{
    return Database::get('db')->table('tags')->eq('id', $tag)->remove();
}

// Remove all tags
function remove_feed($feed)
{
    return Database::get('db')->table('feeds2tags')->eq('feed', $feed)->remove();
}

// Remove all tags
function remove_all()
{
    return Database::get('db')->table('tags')->remove();
}
