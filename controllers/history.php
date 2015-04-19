<?php

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoFarad\Template;

// Display history page
Router\get_action('history', function() {

    $offset = Request\int_param('offset', 0);
    $group_id = Request\int_param('group_id', null);
    $feed_id = Request\int_param('feed_id', null);

    // feed_id and group_id can be present at the same time, if a feed of a
    // group is viewed. Show only items of this feed instead of the whole group.
    if (!is_null($feed_id)) {
        $feed_ids = array($feed_id);
    }
    elseif (!is_null($group_id)) {
        $feed_ids = Model\Tag\get_feeds_by_tag($group_id);
    }
    else {
        $feed_ids = null;
    }

    $nb_items = Model\Item\count_by_status('read', $feed_ids);
    $items = Model\Item\get_all_by_status(
        'read',
        $feed_ids,
        $offset,
        Model\Config\get('items_per_page'),
        'updated',
        Model\Config\get('items_sorting_direction')
    );

    Response\html(Template\layout('history', array(
        'favicons' => Model\Feed\get_item_favicons($items),
        'original_marks_read' => Model\Config\get('original_marks_read'),
        'feed_id' => $feed_id,
        'group_id' => $group_id,
        'items' => $items,
        'order' => '',
        'direction' => '',
        'display_mode' => Model\Config\get('items_display_mode'),
        'nb_items' => $nb_items,
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'offset' => $offset,
        'items_per_page' => Model\Config\get('items_per_page'),
        'nothing_to_read' => Request\int_param('nothing_to_read'),
        'menu' => 'history',
        'title' => t('History').' ('.$nb_items.')'
    )));
});

// Confirmation box to flush history
Router\get_action('confirm-flush-history', function() {

    $group_id = Request\int_param('group_id', null);
    $feed_id = Request\int_param('feed_id', null);

    Response\html(Template\layout('confirm_flush_items', array(
        'feed_id' => $feed_id,
        'group_id' => $group_id,
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'menu' => 'history',
        'title' => t('Confirmation')
    )));
});

// Flush history
Router\get_action('flush-history', function() {

    $group_id = Request\int_param('group_id', null);
    $feed_id = Request\int_param('feed_id', null);

    // feed_id and group_id can be present at the same time, if a feed of a
    // group is viewed. Flush only items of this feed instead of the whole group.
    if (!is_null($feed_id)) {
        Model\Item\mark_feed_as_removed($feed_id);
        Response\redirect('?action=history&group_id='.$group_id.'&feed_id='.$feed_id);
    }
    elseif (!is_null($group_id)) {
        Model\Item\mark_group_as_removed($group_id);
        Response\redirect('?action=history&group_id='.$group_id);
    }
    else {
        Model\Item\mark_all_as_removed();
        Response\redirect('?action=history');
    }

});
