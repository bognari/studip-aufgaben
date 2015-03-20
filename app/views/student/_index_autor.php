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
<table class="default zebra tablesorter" id="leeroy_tasks">
    <thead>
    <tr class="sortable">
        <th <?= $sort === 'title' ? 'class="sort' . $order . '"' : '' ?> style="width: auto">
            <a href="<?= $controller->url_for('index/index?sort_by=title' . ($order === 'desc' ? '&asc=1' : '')) ?>">
                <?= _('Aufgabe') ?>
            </a>
        </th>

        <th style="width: 140px">
            <?= _('Eigenschaften') ?>
        </th>

        <th <?= $sort === 'startdate' ? 'class="sort' . $order . '"' : '' ?> style="width: 120px;">
            <a href="<?= $controller->url_for('index/index?sort_by=startdate' . ($order === 'desc' ? '&asc=1' : '')) ?>">
                <?= _('Start') ?>
            </a>
        </th>

        <th <?= $sort === 'enddate' ? 'class="sort' . $order . '"' : '' ?> style="width: 120px;">
            <a href="<?= $controller->url_for('index/index?sort_by=enddate' . ($order === 'desc' ? '&asc=1' : '')) ?>">
                <?= _('Ende') ?>
            </a>
        </th>

        <th <?= $sort === 'enddate' ? 'class="sort' . $order . '"' : '' ?> style="width: 70px;">
            <a href="<?= $controller->url_for('index/index?sort_by=enddate' . ($order === 'desc' ? '&asc=1' : '')) ?>">
                <?= _('Status') ?>
            </a>
        </th>

        <th colspan="2" style="text-align: center; width: 80px;">
            <?= _('Arbeit') ?>
        </th>

        <th colspan="2" style="text-align: center; width: 80px;">
            <?= _('Feedback') ?>
        </th>

        <th style="width: 80px; text-align: center"><?= _('Hinweis') ?></th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($tasks as $task) : ?>
        <? $handin = $task->handins->findOneBy('user_id', $GLOBALS['user']->id) ?>
        <?

        if ($handin === null || $handin->task_id !== $task->getId()) {  // create missing entries on the fly
            $handin = Leeroy\Handin::create(array(
                'user_id' => $GLOBALS['user']->id,
                'chdate' => 1,
                'mkdate' => 1,
                'task_id' => $task->getId()
            ));
        }
        ?>
        <tr class="<?= $task->getStatus() ?>">
            <td>
                <a href="<?= $controller->url_for('student/view_student/' . $task['id']) ?>"
                   title="<?= _('Diese Aufgabe anzeigen') ?>">
                    <?= htmlReady($task['title']) ?>
                </a>

            </td>

            <td>
                <?= $task->isRequired() ? Assets::img('icons/16/blue/medal.png', array('alt' => _('Pflichtaufgabe'), 'title' => _('Pflichtaufgabe'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $task->hasMaterial() ? Assets::img('icons/16/blue/staple.png', array('alt' => _('Materialien'), 'title' => _('Materialien'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $task->hasTaskLink() ? Assets::img('icons/16/blue/info-circle.png', array('alt' => _('externer Link'), 'title' => _('externer Link'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $task->hasJobs() ? Assets::img('icons/16/blue/code.png', array('alt' => _('automatische Analysen'), 'title' => _('automatische Analysen'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>

                <?= $handin->hasAnalyticResult() ? Assets::img('icons/16/blue/stat.png', array('alt' => _('Analyse Ergebnisse'), 'title' => _('Analyse Ergebnisse'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $handin->hasTestResult() ? Assets::img('icons/16/blue/unit-test.png', array('alt' => _('Test Ergebnisse'), 'title' => _('Test Ergebnisse'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $handin->hasLinkResult() ? Assets::img('icons/16/blue/log.png', array('alt' => _('Link Ergebnisse'), 'title' => _('Link Ergebnisse'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
            </td>

            <td>
                <?= strftime($timeformat, $task['startdate']) ?>
            </td>
            <td>
                <?= strftime($timeformat, $task['enddate']) ?>
            </td>
            <td>
                <?= _($task->getStatusText()) ?>
            </td>
            <td style="width: 50px; text-align: right">
                <?= (!$handin || $handin->answer === null) ? '0' : strlen($handin->answer) ?>
                <?= Assets::img('icons/16/black/file-text.png', array(
                    'title' => _('Antworttext')
                )) ?>
            </td>
            <td style="width: 40px">
                <?= $handin ? count($handin->files->findBy('type', 'answer')) : 0 ?>
                <?= Assets::img('icons/16/black/files.png', array(
                    'title' => _('Hochgeladene Dateien')
                )) ?>
            </td>
            <td style="width:50px; text-align: right">
                <?= (!$handin || $handin->feedback === null) ? '0' : strlen($handin->feedback) ?>
                <?= Assets::img('icons/16/black/file-text.png', array(
                    'title' => _('Antworttext')
                )) ?>
            </td>
            <td style="width: 40px">
                <?= $handin ? count($handin->files->findBy('type', 'feedback')) : 0 ?>
                <?= Assets::img('icons/16/black/files.png', array(
                    'title' => _('Hochgeladene Dateien')
                )) ?>
            </td>
            <td style="text-align: center">
                <?= ($handin && $handin->hint)
                    ? Assets::img('icons/16/black/file-text.png', array(
                        'title' => _('Für diese Aufgabe wurden Hinweise für Sie hinterlegt!')
                    )) : '-' ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>

<script>
    $('#leeroy_tasks').tablesorter();
</script>
