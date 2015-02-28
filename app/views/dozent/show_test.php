<?php
/**
 *
 *
 * Long description for file (if any)...
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Stephan Mielke
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

$content = array(array(
    'kategorie' => _('Aktionen'),
    'eintrag' => array()));

$infobox = array('picture' => 'infobox/schedules.jpg', 'content' => $content);

?>

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', 'Testergebnisse für ' . $task->title))) ?>

    <br>
    <label> <?= _('Testergebnisse für') ?>  <?= htmlReady($task->title) ?> : </label>

<? if (count($suites) > 0) : ?>
    <? foreach ($suites as $suite) : ?>
        <br>
        <table class="default zebra">
            <thead>
            <tr>
                <th style="min-width: 50px"><?= _('Testname') ?></th>
                <th style="min-width: 50px"><?= _('Status') ?></th>
                <th style="width: 100%"><?= _('Grund des Scheiterns') ?></th>
            </tr>
            </thead>
            <? foreach ($suite->cases as $case) : ?>
                <tr>
                    <td>
                        <?= htmlReady($case->name) ?>
                    </td>

                    <td>
                        <?= htmlReady($case->status) ?>
                    </td>
                    <td>
                        <?= htmlReady($case->errorDetails) ?>
                    </td>
                </tr>
            <? endforeach ?>
            <tbody>
            </tbody>
        </table>
    <? endforeach ?>
<? else : ?>
    <? if ($data->test === 'fail') : ?>
        <?= MessageBox::error(_('Testausführung fehlgeschlagen.')); ?>
    <? endif ?>
    <? if (is_null($data->test)) : ?>
        <?= MessageBox::error(_('Keine Test ausgeführt.')); ?>
    <? endif ?>
<? endif ?>