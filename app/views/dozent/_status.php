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
<? foreach ($group as $group_id => $group_users) : ?>
    <label> <?= $group_names[$group_id] ?>: </label>
    <table class="default zebra">
        <thead>
        <tr>
            <th style="width: 100%"><?= _('TeilnehmerIn') ?></th>
            <th style="min-width: 80px; text-align: center"><?= _('Punkte') ?></th>
            <th colspan="2" style="min-width: 80px; text-align: center"><?= _('in Arbeit') ?></th>
            <th style="min-width: 150px; text-align: center"><?= _('letzte Aktivität') ?></th>
            <? if ($task->enddate <= time()) : ?>
                <th colspan="2" style="min-width: 100px; text-align: center"><?= _('Feedback') ?></th>
            <? endif ?>
            <th style="min-width: 80px; text-align: center"><?= _('Hinweis') ?></th>
            <th style="min-width: 80px; text-align: center"><?= _('Aktionen') ?></th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($group_users as $user) : ?>
            <?
            $handin = $task->handins->findOneBy('user_id', $user->user_id)
            ?>
            <tr>
                <td>
                    <a href="<?= $controller->url_for('dozent/view_dozent/' . $handin->id) ?>">
                        <?= htmlReady(get_fullname($user->user_id)) ?>
                    </a>
                </td>

                <td style="text-align: center">
                    <a href="<?= $controller->url_for('dozent/grading/' . $group_id . '/' . $task->id) ?>">
                        <?= (!$handin || $handin->points === null) ? '-' : htmlReady($handin->points) ?>
                    </a>
                </td>

                <td style="text-align: right">
                    <?= (!$handin || $handin->answer === null) ? '0' : strlen($handin->answer) ?>
                    <?= Assets::img('icons/16/black/file-text.png', array(
                        'title' => _('Antworttext')
                    )) ?>
                </td>
                <td>
                    <?= $handin ? count($handin->files->findBy('type', 'answer')) : 0 ?>
                    <?= Assets::img('icons/16/black/files.png', array(
                        'title' => _('Hochgeladene Dateien')
                    )) ?>
                </td>

                <td>
                    <?= ($handin && $handin->chdate) ? strftime($timeformat, $handin->chdate) : '-' ?>
                </td>
                <? if ($task->enddate <= time()) : ?>
                    <td style="text-align: right">
                        <?= (!$handin || $handin->feedback === null) ? '0' : strlen($handin->feedback) ?>
                        <?= Assets::img('icons/16/black/file-text.png', array(
                            'title' => _('Antworttext')
                        )) ?>
                    </td>
                    <td>
                        <?= $handin ? count($handin->files->findBy('type', 'feedback')) : 0 ?>
                        <?= Assets::img('icons/16/black/files.png', array(
                            'title' => _('Hochgeladene Dateien')
                        )) ?>
                    </td>
                <? endif ?>
                <td style="text-align: center">
                    <?= ($handin && $handin->hint)
                        ? Assets::img('icons/16/black/file-text.png', array(
                            'title' => _('Für diese Aufgabe wurden Hinweise für Sie hinterlegt!')
                        )) : '-' ?>
                </td>
                <td style="text-align: center">
                    <a href="<?= $controller->url_for('dozent/view_dozent/' . $handin->id) ?>">
                        <?= Assets::img('icons/16/black/edit.png', array('title' => _('Diese Aufgabe für diesen Nutzer bearbeiten'))) ?>
                    </a>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endforeach ?>