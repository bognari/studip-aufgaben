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

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', 'Compilerfehler für ' . $task->title))) ?>

    <br>
    <h1> <?= _('Compilerfehler für') ?>  <?= htmlReady($task->title) ?> : </h1>

<? if (is_string($data->log)) : ?>
    <div style="font-family:monospace">
        <?= nl2br(str_replace(' ', '&nbsp;', str_replace('\t', '      ', htmlentities($data->log)))) ?>
    </div>
<? else : ?>
    <?= MessageBox::info(_('Keine Compilerfehler gefunden.')); ?>
<? endif ?>