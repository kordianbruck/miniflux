<div class="page-header">
    <h2><?= t('Confirmation') ?></h2>
</div>

<p class="alert alert-info"><?= t('Do you really want to remove these items from your history?') ?></p>

<div class="form-actions">
    <a href="?action=flush-history<?= is_null($group_id) ? '' : '&amp;group_id='.$group_id ?><?= is_null($feed_id) ? '' : '&amp;feed_id='.$feed_id ?>" class="btn btn-red"><?= t('Remove') ?></a>
    <?= t('or') ?> <a href="?action=history"><?= t('cancel') ?></a>
</div>