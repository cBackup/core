<?php
/** @var $data array */
?>

<li class="dropdown notifications-menu system-message-widget">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell-o"></i>
        <?php if ($data['count'] > 0): ?>
            <span class="label label-warning"><?= $data['count'] ?></span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu pull-right">
        <li class="header"><?= Yii::t('app', 'System messages') ?></li>
        <li>
            <table class="table table-hover" style="margin-bottom: 0">
                <?php if ($data['count'] > 0): ?>
                    <?php foreach ($data['messages'] as $entry): ?>
                        <tr>
                            <td width="35%"><?= $entry['created'] ?></td>
                            <td><div><?= $entry['message'] ?></div></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3"><?= Yii::t('app', 'There are no new system messages.') ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </li>
        <li class="footer">
            <?= \yii\helpers\Html::a(Yii::t('app', 'View all messages'), ['/message']) ?>
        </li>
    </ul>
</li>
