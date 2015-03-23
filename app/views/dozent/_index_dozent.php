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
<table class="default zebra tablesorter">
    <thead>
    <tr class="sortable">
        <th style="width: 60%" <?= $sort === 'title' ? 'class="sort' . $order . '"' : '' ?>>
            <a href="<?= $controller->url_for('index/index?sort_by=title' . ($order === 'desc' ? '&asc=1' : '')) ?>">
                <?= _('Aufgabe') ?>
            </a>
        </th>

        <th style="min-width: 140px">
            <?= _('Eigenschaften') ?>
        </th>

        <th <?= $sort === 'startdate' ? 'class="sort' . $order . '"' : '' ?> style="min-width: 120px">
            <a href="<?= $controller->url_for('index/index?sort_by=startdate' . ($order === 'desc' ? '&asc=1' : '')) ?>">
                <?= _('Start') ?>
            </a>
        </th>

        <th <?= $sort === 'enddate' ? 'class="sort' . $order . '"' : '' ?> style="min-width: 120px">
            <a href="<?= $controller->url_for('index/index?sort_by=enddate' . ($order === 'desc' ? '&asc=1' : '')) ?>">
                <?= _('Ende') ?>
            </a>
        </th>

        <th <?= $sort === 'enddate' ? 'class="sort' . $order . '"' : '' ?>>
            <a href="<?= $controller->url_for('index/index?sort_by=enddate' . ($order === 'desc' ? '&asc=1' : '')) ?>">
                <?= _('Status') ?>
            </a>
        </th>
        <th style="width: 50px"><?= _('Aktionen') ?></th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($tasks as $task) : ?>
        <tr class="<?= $task->getStatus() ?>">
            <td>
                <a href="<?= $controller->url_for('dozent/view_task/' . $task['id']) ?>"
                   title="<?= _('Diese Aufgabe anzeigen') ?>">
                    <?= htmlReady($task['title']) ?>
                </a>

            </td>
            <td>
                <?= $task->isRequired() ? Assets::img('icons/16/blue/medal.png', array('alt' => _('Pflichtaufgabe'), 'title' => _('Pflichtaufgabe'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $task->hasMaterial() ? Assets::img('icons/16/blue/staple.png', array('alt' => _('Materialien'), 'title' => _('Materialien'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $task->hasTaskLink() ? Assets::img('icons/16/blue/info-circle.png', array('alt' => _('externer Link'), 'title' => _('externer Link'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $task->hasJobs() ? Assets::img('icons/16/blue/code.png', array('alt' => _('automatische Analysen'), 'title' => _('automatische Analysen'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>

                <?= $task->hasAnalyticResult() ? Assets::img('icons/16/blue/stat.png', array('alt' => _('Analyse Ergebnisse'), 'title' => _('Analyse Ergebnisse'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $task->hasTestResult() ? Assets::img('icons/16/blue/unit-test.png', array('alt' => _('Test Ergebnisse'), 'title' => _('Test Ergebnisse'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>
                <?= $task->hasLinkResult() ? Assets::img('icons/16/blue/log.png', array('alt' => _('Link Ergebnisse'), 'title' => _('Link Ergebnisse'))) : Assets::img('blank.gif', array('height' => 16, 'width' => 16)) ?>

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
            <td>
                <a href="<?= $controller->url_for('dozent/edit_task/' . $task['id']) ?>"
                   title="<?= _('Diese Aufgabe bearbeiten') ?>">
                    <?= Assets::img('icons/16/blue/edit.png') ?>
                </a>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>