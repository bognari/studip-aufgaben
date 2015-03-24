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

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', array('dozent/view_task/' . $task->getId(), $task['title']), array('dozent/grading/' . $group_id . '/' . $task->getId(), _('Bewertung' . ' ' . $group_name))))) ?>

<? if (is_string($is_success)) : ?>
    <? if ($is_success == 'success') : ?>
        <?= MessageBox::success(_('Alle Punkte wurden gespeichert.')); ?>
    <? else : ?>
        <?= MessageBox::error(_('Es konnten nicht alle Punkte gespeichert werden.')) ?>
    <? endif ?>
<? endif ?>

<br>
<h2> <?= _('Punkteübersicht für ') ?> <?= htmlReady($group_name) ?> <?= _('mit') ?> <?= htmlReady($task->title) ?>
    : </h2>
<form action="<?= $controller->url_for('dozent/grading_save/' . $task->id . '/' . $group_id) ?>" method="post">
    <table class="default zebra">
        <thead>
        <tr>
            <th style="min-width: 80px"><?= _('TeilnehmerIn') ?></th>
            <th style="min-width: 80px"><?= _('Gesamtpunkte') ?></th>
            <th style="min-width: 80px; text-align: left"><?= _('Punkte') ?></th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($handins as $handin) : ?>
            <tr>

                <td>
                    <?= htmlReady(get_fullname($handin->user_id)) ?>
                </td>

                <td>
                    <?= htmlReady(Leeroy\Handin::getTotalPoints($seminar_id, $handin->user_id)) ?>
                </td>

                <td style="text-align: left">
                    <input name="<?= $handin->id ?>" type="text" title="<?= _('Punkte') ?>"
                           value="<?= $handin->points === null ? '' : htmlReady($handin->points) ?>">
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