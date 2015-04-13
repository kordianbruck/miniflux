<div class="page-header">
    <h2><?= t('Edit subscription') ?></h2>
    <ul>
        <li><a href="?action=add"><?= t('add') ?></a></li>
        <li><a href="?action=feeds"><?= t('feeds') ?></a></li>
        <li><a href="?action=import"><?= t('import') ?></a></li>
        <li><a href="?action=export"><?= t('export') ?></a></li>
    </ul>
</div>

<form method="post" action="?action=edit-feed" autocomplete="off">

    <?= Helper\form_hidden('id', $values) ?>

    <?= Helper\form_label(t('Title'), 'title') ?>
    <?= Helper\form_text('title', $values, $errors, array('required')) ?>

    <?= Helper\form_label(t('Website URL'), 'site_url') ?>
    <?= Helper\form_text('site_url', $values, $errors, array('required', 'placeholder="http://..."')) ?>

    <?= Helper\form_label(t('Feed URL'), 'feed_url') ?>
    <?= Helper\form_text('feed_url', $values, $errors, array('required', 'placeholder="http://..."')) ?>

    <?= Helper\form_checkbox('rtl', t('Force RTL mode (Right-to-left language)'), 1, isset($values['rtl']) ? $values['rtl'] : false) ?><br />

    <?= Helper\form_checkbox('download_content', t('Download full content'), 1, isset($values['download_content']) ? $values['download_content'] : false) ?><br />

    <?= Helper\form_checkbox('cloak_referrer', t('Cloak the image referrer'), 1, isset($values['cloak_referrer']) ? $values['cloak_referrer'] : false) ?><br />

    <?= Helper\form_checkbox('enabled', t('Activated'), 1, isset($values['enabled']) ? $values['enabled'] : false) ?><br />

    <?= Helper\form_label(t('Tags'), 'tags'); ?>

    <div id="taglist">
        <?php foreach ($tags as $tag_id => $tag_name): ?>
            <?= Helper\form_checkbox('feed_tag_ids[]', $tag_name, $tag_id, in_array($tag_id, $values['feed_tag_ids']), 'hide') ?>
        <?php endforeach ?>
        <?= Helper\form_text('create_tag', $values, array(), array('placeholder="'.t('add a new tag').'"')) ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Save') ?></button>
        <?= t('or') ?> <a href="?action=feeds"><?= t('cancel') ?></a>
    </div>
</form>
<div class="form-actions">
    <a href="?action=confirm-remove-feed&amp;feed_id=<?= $values['id'] ?>" class="btn btn-red"><?= t('Remove this feed') ?></a>
</div>
