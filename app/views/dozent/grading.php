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
<label> <?= _("Punkteübersicht für ") ?> <?= $group_name ?> <?= _("mit") ?> <?= $task->Title ?> : </label>
<form action="<?= $controller->url_for('dozent/grading_save/' . $group_id . '/' . $task->id) ?>" method="post">
    <table class="default zebra">
        <thead>
        <tr>
            <th style="min-width: 80px"><?= _('TeilnehmerIn') ?></th>
            <th style="min-width: 80px; text-align: left"><?= _('Punkte') ?></th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($handins as $handin) : ?>
            <tr>

                <td>
                    <?= get_fullname($handin->user_id) ?>
                </td>

                <td style="text-align: left">
                    <input name="<?= $handin->id ?>" type="text" title="Punkte"
                           value="<?= is_null($handin->points) ? '' : $handin->points ?>">
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>

    <div class="buttons">
        <div class="button-group">
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('dozent/view_task/' . $task->id)) ?>
        </div>
    </div>

</form>