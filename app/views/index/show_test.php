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

<? if ($from === 'task') : ?>
    <?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', array('dozent/view_task/' . $task->getId(), $task['title']), array('dozent/show_test/' . $data->getId(), _('Testergebnisse'))))) ?>
<? else : ?>
    <? if (Leeroy\Perm::has('new_task', $this->seminar_id)) : ?>
        <?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', array('dozent/view_task/' . $task->getId(), $task['title']), array('dozent/view_dozent/' . $data->getId(), get_fullname($data->user_id)), array('index/show_test/' . $data->getId(), _('Testergebnisse'))))) ?>
    <? else : ?>
        <?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', array('student/view_student/' . $task->getId(), $task['title']), array('index/show_test/' . $data->getId(), _('Testergebnisse'))))) ?>
    <? endif ?>
<? endif ?>

    <br>
    <h1> <?= _('Testergebnisse für') ?>  <?= htmlReady($task->title) ?> : </h1>

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
    <? if ($data->test === null) : ?>
        <?= MessageBox::error(_('Keine Test ausgeführt.')); ?>
    <? endif ?>
<? endif ?>