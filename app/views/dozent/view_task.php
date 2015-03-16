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
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      Stephan Mielke
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

$content = array();

if ($task->hasTaskLink()) {
    $content_link = array(
        'kategorie' => _('Link'),
        'eintrag' => array(
            array(
                'icon' => 'icons/16/black/link-extern.png',
                'text' => sprintf('%s' . _('Link') . '%s', '<a target="_blank" href="' . $task->task_link . '">', '</a>')
            )
        ));

    array_push($content, $content_link);
}

if ($task->hasMaterial()) {
    $entry_material = array();

    foreach ($task->files as $file) {
        array_push($entry_material, array(
            'icon' => 'icons/16/black/staple.png',
            'text' => sprintf('%s' . htmlReady($file->document->name) . '%s', '<a target="_blank" href="' . GetDownloadLink($file->document->getId(), $file->document->name) . '">', '</a>')
        ));
    }

    $content_material = array(
        'kategorie' => _('Meterialien'),
        'eintrag' => $entry_material
    );

    array_push($content, $content_material);
}
/*if ($task->lastJob == "fail") {
    $content_fail = array(
        'kategorie' => _('FEHLER'),
        'eintrag' => array(
            array(
                'icon' => 'icons/16/red/decline.png',
                'text' => sprintf(_('Auswertungen sind Fehlgeschlagen')),
            ))
    );

    array_push($content, $content_fail);
}*/
if ($task->hasLinkResult() || $task->hasAnalyticResult() || $task->hasTestResult() || $task->hasLog()) {
    $entry_result = array();
    if ($task->hasLinkResult()) {
        array_push($entry_result, array(
            'icon' => 'icons/16/black/log.png',
            'text' => sprintf('%s' . _('Auswertung') . '%s', '<a target="_blank" href="' . $task->link . '">', '</a>')
        ));
    }

    if ($task->hasAnalyticResult()) {
        array_push($entry_result, array(
            'icon' => 'icons/16/black/stat.png',
            'text' => sprintf('%s' . _('Analyseergebnisse') . ' (%s)%s', '<a href="' . $controller->url_for('dozent/show_analytics/' . $task->id) . '">', $task->getAnalyticWarnings(), '</a>')
        ));
    }

    if ($task->hasTestResult()) {
        array_push($entry_result, array(
            'icon' => 'icons/16/black/unit-test.png',
            'text' => sprintf('%s' . _('Testergebnisse') . ' (%s)%s', '<a href="' . $controller->url_for('dozent/show_test/' . $task->id) . '">', $task->getTestErrors(), '</a>')
        ));
    }

    if ($task->hasLog()) {
        array_push($entry_result, array(
            'icon' => 'icons/16/red/decline.png',
            'text' => sprintf('%s' . _('Compilerfehler') . '%s', '<a href="' . $controller->url_for('dozent/show_log/' . $task->id) . '">', '</a>')
        ));
    }

    $content_result = array(
        'kategorie' => _('Ergebnisse'),
        'eintrag' => $entry_result
    );

    array_push($content, $content_result);
}

$entry_bewertung = array();

foreach ($group_names as $group_id => $group_name) {
    array_push($entry_bewertung, array(
        'icon' => 'icons/16/black/evaluation.png',
        'text' => '<a href="' . $controller->url_for('dozent/grading/' . $group_id . '/' . $task->id) . '">' .
            htmlReady($group_name) . '</a>'
    ));
}

$content_bewertung = array(
    'kategorie' => _('Bewertung'),
    'eintrag' => $entry_bewertung
);
array_push($content, $content_bewertung);

$entry_dowload = array(
    array(
        'icon' => 'icons/16/black/file-archive.png',
        'text' => '<a href="' . $controller->url_for('dozent/download/gaul/false/' . $task->id) . '">' . _('Alle Gruppen') . '</a>'
    ));

foreach ($group_names as $group_id => $group_name) {
    array_push($entry_dowload, array(
        'icon' => 'icons/16/black/file-archive.png',
        'text' => '<a href="' . $controller->url_for('dozent/download/aul/' . $group_id . '/' . $task->id) . '">' .
            htmlReady($group_name) . '</a>'
    ));
}

$content_dowload = array(
    'kategorie' => _('Download'),
    'eintrag' => $entry_dowload
);
array_push($content, $content_dowload);


if (count($content) === 0) {
    $content = array(array(
        'kategorie' => _('Aktionen'),
        'eintrag' => array()));
}

$infobox = array('picture' => 'infobox/schedules.jpg', 'content' => $content);
?>

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', $task['title']))) ?>

<?= $this->render_partial('index/_task_details') ?>

<br>

<div class="buttons">
    <div class="button-group">
        <?= Studip\LinkButton::createEdit(_('Bearbeiten'), $controller->url_for('dozent/edit_task/' . $task['id'])) ?>
        <?= Studip\LinkButton::createDelete(_('Löschen'), 'javascript:STUDIP.Leeroy.createQuestion("' .
            _('Sind Sie sicher, dass Sie die komplette Aufgabe löschen möchten?') . '",
            "' . $controller->url_for('dozent/delete_task/' . $task['id']) . '");') ?>
    </div>
</div>

<?= $this->render_partial('dozent/_status.php', compact('participants')) ?>
