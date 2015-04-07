
<?= Helper\form_multi_select($tags, 'id', 'title', function($e) {
    return !empty($e['feed']);
}) ?>
