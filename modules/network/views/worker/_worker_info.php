<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (c) $today.yea, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * cBackup is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @var $data \app\models\Worker
 */
use yii\helpers\Html;
use yii\helpers\Url;
?>

<?php if (is_null($data)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="callout callout-info" style="margin-bottom: 0;">
                <p><?= Yii::t('network', 'Specified worker was not found.') ?></p>
            </div>
        </div>
    </div>
<?php else: ?>
    <table class="table text-fus">
        <tr>
            <th><?= Yii::t('app', 'Name') ?></th>
            <td><?= $data->name ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('network', 'Task') ?></th>
            <td><?= \yii\helpers\Html::a($data->task_name, ['/network/task/edit', 'name' => $data->task_name]) ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('network', 'Protocol') ?></th>
            <td><?= mb_strtoupper($data->get) ?></td>
        </tr>
        <?php if(!empty($data->description)): ?>
        <tr>
            <th><?= Yii::t('app', 'Description') ?></th>
            <td><?= $data->description ?></td>
        </tr>
        <?php endif; ?>
        <?php if(!empty($data->sortedJobs)): ?>
        <tr>
            <td colspan="2">
                <div class="well sequence">
                <?php
                    foreach ($data->sortedJobs as $job){

                        $command_prefix  = '';
                        $command_postfix = '';

                        if( $data->get == 'snmp' ) {
                            $snmp_type       = preg_replace(['/^he(x).*/i', '/^(i)n.*/', '/^ip_(a).*/', '/^(n).*/', '/^o.+_(s).*/', '/^(u).*/'], '$1', $job->snmp_set_value_type);
                            $command_prefix  = "snmp{$job->snmp_request_type}&nbsp;";
                            $snmp_data       = !empty($job->snmp_set_value) ? "'{$job->snmp_set_value}'" : '';
                            $command_postfix = " $snmp_type $snmp_data";
                        }

                        echo Html::tag('span', "{$command_prefix}{$job->command_value}{$command_postfix}", [
                            'class'         => 'sequence-command',
                            'data-ajax-url' => Url::to(['/network/worker/ajax-get-job-description', 'job_id' => $job->id])
                        ]);
                        echo "<br>";

                    }
                ?>
                </div>
                <div class="well text-success" id="sequence_desc">&nbsp;</div>
            </td>
        </tr>
        <?php endif; ?>
    </table>
<?php endif; ?>
