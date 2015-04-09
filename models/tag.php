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
 * @param integer $feed_id id of the feed
 * @return array
 */
function get_feed_tag_ids($feed_id)
{
    return Database::get('db')
            ->table('tag')
            ->join('feed_tag', 'tag_id', 'id')
            ->eq('feed_id', $feed_id)
            ->findAllByColumn('id');
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

    // check if the tag already exists
    $tag_id = get_tag_id($name);

    // create tag if missing
    if ($tag_id === false) {
       Database::get('db')
                ->table('tag')
                ->insert($data);

        $tag_id = get_tag_id($name);
    }

    return $tag_id;
}

/**
 * Add tags to feed
 *
 * @param integer $feed_id feed id
 * @param array $tags array of tag ids
 * @return boolean true on success, false on error
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
 * @return boolean true on success, false on error
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

    if (! empty($tags)) {
        Database::get('db')
            ->table('tag')
            ->in('id', $tags)
            ->remove();
    }
}

/**
 * Update feed tagging information
 *
 * @param integer $feed_id id of the feed to update
 * @param array $tag_ids valid tags ids for feed
 * @param string $create_tag tag to create and assign to feed
 * @return boolean
 */
function update_feed_tags($feed_id, $tag_ids, $create_tag = '')
{
    if ($create_tag !== '') {
        $id = create($create_tag);

        if ($id === false) {
            return false;
        }

        $tag_ids[] = $id;
    }

    $assigned = get_feed_tag_ids($feed_id);
    $superfluous = array_diff($assigned, $tag_ids);
    $missing = array_diff($tag_ids, $assigned);

    // remove no longer assigned tags from feed
    if (! empty($superfluous) && ! remove($feed_id, $superfluous)) {
        return false;
    }

    // add requested tags to feed
    if (! empty($missing) && ! add($feed_id, $missing)) {
        return false;
    }

    // cleanup
    purge_tags();

    return true;
}
