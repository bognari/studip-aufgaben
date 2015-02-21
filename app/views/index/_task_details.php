<?
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
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      Stephan Mielke
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */
?>
<h2><?= htmlReady($task['title']) ?>  <?= $task->isRequired() ? _(" - PFLICHTAUFGABE") : '' ?></h2>

<div class="mark">
    <?= formatReady($task['content']) ?><br>
    <br>

    <hr>

    <b>Aufgabe bearbeitbar bis:</b><br>
    <?= strftime($timeformat, $task['enddate']) ?> <?= _('Uhr') ?><br>

    <? if ($task->allow_text && $task->allow_files) : ?>
        <br><?= _('Texteingabe und Dateiupload erlaubt') ?><br>
    <? elseif ($task->allow_text) : ?>
        <br><?= _('Texteingabe erlaubt') ?><br>
    <? elseif ($task->allow_files) : ?>
        <br><?= _('Dateiupload erlaubt') ?><br>
    <? endif ?>

    <? /*
    <? if ($task->send_mail) : ?>
        <br><?= _('Es wird eine Mail an alle TeilnehmerInnen verschickt, sobald die Aufgabe sichtbar ist.') ?><br>
    <? endif ?> */ ?>
</div>
