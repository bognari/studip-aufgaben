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
    <?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', array('dozent/view_task/' . $task->getId(), $task['title']), array('dozent/show_log/' . $data->getId(), _('Compilerfehler'))))) ?>
<? else : ?>
    <? if (Leeroy\Perm::has('new_task', $this->seminar_id)) : ?>
        <?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', array('dozent/view_task/' . $task->getId(), $task['title']), array('dozent/view_dozent/' . $data->getId(), get_fullname($data->user_id)), array('index/show_log/' . $data->getId(), _('Compilerfehler'))))) ?>
    <? else : ?>
        <?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', array('student/view_student/' . $task->getId(), $task['title']), array('index/show_log/' . $data->getId(), _('Compilerfehler'))))) ?>
    <? endif ?>
<? endif ?>

    <br>
    <h1> <?= _('Compilerfehler für') ?>  <?= htmlReady($task->title) ?> : </h1>

<? if (is_string($data->log)) : ?>
    <div style="font-family:monospace">
        <?= nl2br(str_replace(' ', '&nbsp;', str_replace('\t', '      ', htmlentities($data->log)))) ?>
    </div>
<? else : ?>
    <?= MessageBox::info(_('Keine Compilerfehler gefunden.')); ?>
<? endif ?>