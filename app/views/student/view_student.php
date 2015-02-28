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
            'text' => sprintf('%s' . $file->document->name . '%s', '<a target="_blank" href="' . GetDownloadLink($file->document->getId(), $file->document->name) . '">', '</a>')
        ));
    }

    $content_material = array(
        'kategorie' => _('Meterialien'),
        'eintrag' => $entry_material
    );

    array_push($content, $content_material);
}

# TODO andere links setzen !
if ($handin->hasPoints() || $handin->hasLinkResult() || $handin->hasAnalyticResult() || $handin->hasTestResult() || $handin->hasLog()) {
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
            'text' => sprintf('%s' . _('Auswertung') . '%s', '<a target="_blank" href="' . $handin->link . '">', '</a>'),
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

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', $task['title']))) ?>

<?= $this->render_partial('index/_task_details') ?>

<? if ($handin['hint']) : ?>
    <br>
    <div class="mark">
        <b><?= _('Hinweis des DozentIn') ?>:</b><br>
        <br>
        <?= formatReady($handin->hint) ?>
    </div>
<? endif ?>

<? if ($task->allow_text) : ?>
    <br>
    <? if ($task->enddate < time()) : ?>
        <b><?= _('Antworttext') ?></b><br>
        <? if ($handin->answer) : ?>
            <br>
            <?= formatReady($handin->answer) ?>
        <? else : ?>
            <span class="empty_text"><?= _('Es wurde keine Antwort eingegeben') ?></span>
        <? endif ?>
        <br><br>
    <? else : ?>
        <?= $this->render_partial('index/_edit_text', array(
            'form_route' => 'student/update_student/' . $task->getId() . '/' . $handin->getId(),
            'cancel_route' => 'student/view_student/' . $task->getId(),
            'name' => _('Antworttext'),
            'field' => 'answer',
            'text' => $handin->answer
        )) ?>
    <? endif ?>
<? endif ?>

<? if ($task['allow_files']) : ?>
    <?= $this->render_partial('index/_file_list', array(
        'files' => $handin->files->findBy('type', 'answer'),
        'edit' => ($task->enddate >= time()),
        'url' => 'file/handin_file',
        'max_file' => 1,
        'id' => $handin->id,
        'open_analytic' => $task->hashUploadTrigger()
    )) ?>
<? endif ?>


<br>
<div class="mark">
    <b><?= _('Feedback DozentIn') ?>:</b><br>
    <? if ($handin->feedback) : ?>
        <?= formatReady($handin->feedback) ?>
    <? else : ?>
        <span class="empty_text"><?= _('Noch kein Feedback vorhanden') ?></span>
    <? endif ?>
    <br>

    <br>
    <? $files = $handin->files->findBy('type', 'feedback') ?>
    <? if (count($files)) : ?>
        <?= $this->render_partial('index/_file_list', compact('files')) ?>
    <? endif ?>
</div>