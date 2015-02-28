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

if ($handin->lastJob === 'fail') {
    $content_fail = array(
        'kategorie' => _('FEHLER'),
        'eintrag' => array(
            array(
                'icon' => 'icons/16/red/decline.png',
                'text' => sprintf(_('Auswertungen sind Fehlgeschlagen'))
            ))
    );

    array_push($content, $content_fail);
}

if ($handin->hasPoints() || $handin->hasLinkResult() || $handin->hasAnalyticResult() || $handin->hasTestResult()) {
    $entry_result = array();
    if ($handin->hasPoints()) {
        array_push($entry_result, array(
            'icon' => 'icons/16/black/doctoral_cap.png',
            'text' => sprintf(_('Erreichte Punkte:') . ' %s', $handin->points)
        ));
    }

    if ($handin->hasLinkResult()) {
        array_push($entry_result, array(
            'icon' => 'icons/16/black/log.png',
            'text' => sprintf('%s' . _('Auswertung') . '%s', '<a target="_blank" href="' . $handin->link . '">', '</a>')
        ));
    }

    if ($handin->hasAnalyticResult()) {
        array_push($entry_result, array(
            'icon' => 'icons/16/black/stat.png',
            'text' => sprintf('%s' . _('Analyseergebnisse') . ' (%s)%s', '<a href="' . $controller->url_for('index/show_analytics/' . $handin->id) . '">', $handin->getAnalyticWarnings(), '</a>')
        ));
    }

    if ($handin->hasTestResult()) {
        array_push($entry_result, array(
            'icon' => 'icons/16/black/unit-test.png',
            'text' => sprintf('%s' . _('Testergebnisse') . ' (%s)%s', '<a href="' . $controller->url_for('index/show_test/' . $handin->id) . '">', $handin->getTestErrors(), '</a>')
        ));
    }

    if ($handin->hasLog()) {
        array_push($entry_result, array(
            'icon' => 'icons/16/red/decline.png',
            'text' => sprintf('%s' . _('Compilerfehler') . '%s', '<a href="' . $controller->url_for('index/show_log/' . $handin->id) . '">', '</a>')
        ));
    }

    $content_result = array(
        'kategorie' => _('Ergebnisse'),
        'eintrag' => $entry_result
    );

    array_push($content, $content_result);
}


if (count($content) === 0) {
    $content = array(array(
        'kategorie' => _('Aktionen'),
        'eintrag' => array()));
}

$infobox = array('picture' => 'infobox/schedules.jpg', 'content' => $content);
?>

<? $this->render_partial('index/_breadcrumb', array('path' => array(
    'overview', array('dozent/view_task/' . $task->getId(), $task['title']), get_fullname($handin->user_id)))) ?>

<?= $this->render_partial('index/_task_details') ?>

<? if ($task->enddate <= time()) : ?>
    <? if ($handin->hint) : ?>
        <br>
        <div class="mark">
            <b><?= _('Hinweis für diese(n) Teilnehmer(in)') ?>:</b>
            <?= tooltipIcon(_('Sie können den Hinweistext nicht mehr verändern, da die Aufgabe bereits beendet ist!'), true) ?>
            <br>
            <br>
            <?= formatReady($handin->hint) ?>
        </div>
    <? endif ?>
    <!-- no edit allowed after the task has ended! -->
<? else : ?>
    <br>
    <?= $this->render_partial('index/_edit_text', array(
        'form_route' => 'dozent/update_dozent/' . $handin->getId(),
        'cancel_route' => 'dozent/view_dozent/' . $handin->getId(),
        'name' => _('Hinweis für diese(n) Teilnehmer(in)'),
        'field' => 'hint',
        'text' => $handin->hint
    )) ?>
<? endif ?>


<? if ($task->startdate <= time()) : ?>
    <? if ($task['allow_text']) : ?>
        <br>
        <div class="mark">
            <b><?= _('Antworttext') ?>:</b><br>
            <? if (!$handin->answer) : ?>
                <br>
                <span class="empty_text"><?= _('Es wurde noch keine Antwort eingegeben.') ?></span>
            <? else : ?>
                <?= formatReady($handin->answer) ?>
            <? endif ?>
            <br>
        </div>
    <? endif ?>

    <? if ($task['allow_files']) : ?>
        <br>
        <? $files = $handin->files->findBy('type', 'answer') ?>
        <? if (count($files)) : ?>
            <?= $this->render_partial('index/_file_list', compact('files')) ?>
        <? endif ?>
    <? endif ?>

    <? if ($task->enddate <= time()) : ?>
        <br>
        <?= $this->render_partial('index/_edit_text', array(
            'form_route' => 'dozent/update_dozent/' . $handin->getId(),
            'cancel_route' => 'dozent/view_dozent/' . $handin->getId(),
            'name' => _('Feedback'),
            'field' => 'feedback',
            'text' => $handin->feedback
        )) ?>
    <? endif ?>

    <br>
    <?= $this->render_partial('index/_file_list', array(
        'files' => $handin->files->findBy('type', 'feedback'),
        'edit' => true,
        'url' => 'file/handin_file',
        'id' => $handin->id
    )) ?>
<? endif ?>