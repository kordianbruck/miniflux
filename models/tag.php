<?php

namespace Model\Tag;

use PicoDb\Database;

//Get all tags which exsist and are linked to this feed
function get_feed_tags($feed)
{
    return Database::get('db')->execute('SELECT * FROM tags t LEFT JOIN feeds2tags f2t ON (f2t.tag=t.id AND f2t.feed=?)', array($feed))->fetchAll();
}

// Update feed tagging information
function update_feed_tags(array $values)
{
    //First remove all tags
    remove_feed_tags($values['id']);

    //Then re-add those which were still selected
    foreach ($values['tag'] as $e){
        if(!empty($e)){
            Database::get('db')->table('feeds2tags')->insert(array('feed'=>$values['id'], 'tag'=>$e));
        }
    }

    //Delete tags which don't have an assigned feed
    purge_tags();

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
function remove_feed_tags($feed)
{
    return Database::get('db')->table('feeds2tags')->eq('feed', $feed)->remove();
}

// Remove all tags
function remove_all()
{
    return Database::get('db')->table('tags')->remove();
}

// Remove tags which are not used
function purge_tags()
{
    $orphanTags = Database::get('db')->table('tags')->join('feeds2tags', 'tag', 'id')->isnull('feed')->findAll();
    foreach ($orphanTags as $value) {
        if(!empty($value['id'])){
            remove($value['id']);
        }
    }
}