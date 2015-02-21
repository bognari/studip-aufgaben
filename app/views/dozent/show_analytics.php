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

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', "Punkteübersicht für " . $group_name))) ?>

    <br>
    <label> <?= _("Analyseergebnisse für ") ?>  <?= $task->Title ?> : </label>

<? if (!empty($files)) : ?>
    <? foreach ($files as $name => $file) : ?>
        <br>
        <br>
        <label> <?= _("Datei: ") ?>  <?= $name ?> : </label>
        <table class="default zebra">
            <thead>
            <tr>
                <th style="min-width: 50px"><?= _('Zeile') ?></th>
                <th style="width: 100%"><?= _('Fehler') ?></th>
            </tr>
            </thead>
            <? foreach ($file as $warning) : ?>
                <tr>
                    <td>
                        <?= $warning->primaryLineNumber ?>
                    </td>

                    <td>
                        <?= $warning->message ?>
                    </td>
                </tr>
            <? endforeach ?>
            <tbody>
            </tbody>
        </table>
    <? endforeach ?>
<? else : ?>
    <? if ($data->analytic == "fail") : ?>
        <?= MessageBox::error(_('Analyse fehlgeschlagen ')); ?>
    <? else : ?>
        <? if (is_null($data->analytic)) : ?>
            <?= MessageBox::error(_('Keine Analyse ausgeführt')); ?>
        <? else : ?>
            <?= MessageBox::info(_('Die Analyse konnte keine Fehler finden :)')); ?>
        <? endif ?>
    <? endif ?>
<? endif ?>