<?php

namespace Model\Tag;

use PicoDb\Database;

/**
 * Get all tags
 *
 * @return array
 */
function get_all()
{
    return Database::get('db')
            ->hashtable('tag')
            ->orderBy('name')
            ->getAll('id','name');
}

/**
 * Get all tags assigned to feed
 *
 * @param integer $feed_id ID of the feed
 * @return array
 */
function get_feed_tags($feed_id)
{
    return Database::get('db')
            ->hashtable('tag')
            ->join('feed_tag', 'tag_id', 'id')
            ->eq('feed_id', $feed_id)
            ->getAll('id', 'name');
}

/**
 * Get the id of tag
 *
 * @param string $name tag name
 * @return mixed tag id or false if not found
 */
function get_tag_id($name)
{
    return Database::get('db')
            ->table('tag')
            ->eq('name', $name)
            ->findOneColumn('id');
}

/**
 * Add tag to the Database
 *
 * Returns either the id of the new tag or the id of an existing tag with the
 * same name
 *
 * @param string $name tag name
 * @return mixed id of the created tag or false on error
 */
function create($name)
{
    $data = array('name' => $name);

    // create tag if missing
    if (get_tag_id($name) === false) {
       Database::get('db')
                ->table('tag')
                ->insert($data);
    }

    // return the id of the created tag
    return get_tag_id($name);
}

/**
 * Add tags to feed
 *
 * @param integer $feed_id feed id
 * @param array $tags array of tag ids
 * @return boolean
 */
function add($feed_id, $tags)
{
    foreach ($tags as $tag){
        $data = array('feed_id' => $feed_id, 'tag_id' => $tag);

        $result = Database::get('db')
                ->table('feed_tag')
                ->insert($data);

        if ($result === false) {
            return false;
        }
    }

    return true;
}

/**
 * Remove tags from feed
 *
 * @param integer $feed_id id of the feed
 * @param array $tags array of tag ids
 * @return boolean
 */
function remove($feed_id, $tags)
{
    return Database::get('db')
            ->table('feed_tag')
            ->eq('feed_id', $feed_id)
            ->in('tag_id', $tags)
            ->remove();
}

/**
 * Purge orphaned tags from database
 */
function purge_tags()
{
    $tags = Database::get('db')
                ->table('tag')
                ->join('feed_tag', 'tag_id', 'id')
                ->isnull('feed_id')
                ->findAllByColumn('id');

    if (!empty($tags)) {
        Database::get('db')
            ->table('tag')
            ->in('id', $tags)
            ->remove();
    }
}

/**
 * Update feed tagging information
 *
 * @param array $values
 * @return boolean
 */
function update_feed_tags(array $values)
{
    $tags = array();

    if (isset($values['tags'])) {
        $tags = $values['tags'];
    }

    //create a new tag
    if (isset($values['create_tag']) && $values['create_tag'] !== '') {
        $id = create($values['create_tag']);

        if ($id === false) {
            return false;
        }

        $tags[] = $id;
    }

    $assigned = array_keys(get_feed_tags($values['id']));
    $superfluous = array_diff($assigned, $tags);
    $missing = array_diff($tags, $assigned);

    // remove no longer assigned tags from feed
    if (! empty($superfluous) && ! remove($values['id'], $superfluous)) {
        return false;
    }

    // add requested tags to feed
    if (! empty($missing) && ! add($values['id'], $missing)) {
        return false;
    }

    // cleanup
    purge_tags();

    return true;
}