<?php
/**
 * filename - Short description for file
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

if (Leeroy\Perm::has('new_task', $seminar_id) || Leeroy\Perm::has('config', $seminar_id)) :
    $infobox_entrys[] = array();
    $i = 0;

    if (Leeroy\Perm::has('config', $seminar_id)) :
        $infobox_entrys[$i++] = array(
            'icon' => 'icons/16/black/info.png',
            'text' => sprintf(_('%sJenkins Konfiguration%s'), '<a href="' . $controller->url_for('dozent/config_jenkins') . '">', '</a>')
        );
        $infobox_entrys[$i++] = array(
            'icon' => 'icons/16/black/info.png',
            'text' => sprintf(_('%sZusatzdaten und Gruppen Konfiguration%s'), '<a href="' . $controller->url_for('dozent/config_aux') . '">', '</a>')
        );
    endif;

    if ($jenkins !== null && Leeroy\Perm::has('new_task', $seminar_id)) :
        $infobox_entrys[$i++] = array(
            'icon' => 'icons/16/black/info.png',
            'text' => sprintf(_('%sNeue Aufgabe anlegen%s'), '<a href="' . $controller->url_for('dozent/new_task') . '">', '</a>')
        );
        $infobox_entrys[$i++] = array(
            'icon' => 'icons/16/black/info.png',
            'text' => sprintf(_('%sExport (CSV)%s'), '<a href="' . $controller->url_for('dozent/csv') . '">', '</a>')
        );
        $infobox_entrys[$i++] = array(
            'icon' => 'icons/16/black/info.png',
            'text' => sprintf(_('%sExport (CSV - de)%s'), '<a href="' . $controller->url_for('dozent/csv/de') . '">', '</a>')
        );
    endif;

    $infobox_content[] = array(
        'kategorie' => _('Aufgaben'),
        'eintrag' => $infobox_entrys
    );

    if (Leeroy\Perm::has('new_task', $seminar_id)) {
        $entry_dowload = array(
            array(
                'icon' => 'icons/16/black/file-archive.png',
                'text' => '<a href="' . $controller->url_for('dozent/download') . '">' . _('Alle Gruppen') . '</a>'
            ));

        $groups = Leeroy_CourseMember::getGroupsForCourse($seminar_id);
        $group_names = $groups['names'];

        if (count($group_names) > 0) {
            natsort($group_names);
        }

        foreach ($group_names as $group_id => $group_name) {
            array_push($entry_dowload, array(
                'icon' => 'icons/16/black/file-archive.png',
                'text' => '<a href="' . $controller->url_for('dozent/download/taul/' . $group_id) . '">' .
                    $group_name . '</a>'
            ));
        }

        $content_dowload = array(
            'kategorie' => _('Download'),
            'eintrag' => $entry_dowload
        );
        array_push($infobox_content, $content_dowload);
    } else {
    }


else :
    if ($jenkins !== null) :
        $infobox_content[] = array(
            'kategorie' => _('Informationen'),
            'eintrag' => array(
                array(
                    'icon' => 'icons/16/black/info.png',
                    'text' => 'Gesamtpunkte: ' . htmlReady(Leeroy\Handin::getTotalPoints($seminar_id, $GLOBALS['user']->id))
                )
            )
        );
    else :
        $infobox_content[] = array(
            'kategorie' => _('Warnung'),
            'eintrag' => array(
                array(
                    'icon' => 'icons/16/black/info.png',
                    'text' => 'Die Konfiguration ist noch nocht abgeschlossen!'
                )
            )
        );
    endif;
endif;

array_push($infobox_content, array(
    'kategorie' => _('Legende'),
    'eintrag' => array(
        array(
            'icon' => 'icons/16/black/medal.png',
            'text' => 'Pflichtaufgabe'
        ),
        array(
            'icon' => 'icons/16/black/staple.png',
            'text' => 'Materialien vorhanden'
        ),
        array(
            'icon' => 'icons/16/black/info-circle.png',
            'text' => 'Weitere Informationen verlinkt'
        ),
        array(
            'icon' => 'icons/16/black/code.png',
            'text' => 'Aufgabe wird mittels automatisch kontrolliert'
        ),
        array(
            'icon' => 'icons/16/black/stat.png',
            'text' => 'Sourcecode-Analyse vorhanden'
        ),
        array(
            'icon' => 'icons/16/black/unit-test.png',
            'text' => 'Test Analyse vorhanden'
        ),
        array(
            'icon' => 'icons/16/black/log.png',
            'text' => 'Externe Analyse vorhanden'
        )
    )
));

#var_dump($infobox_content);

$infobox = array('picture' => 'infobox/schedules.jpg', 'content' => $infobox_content);
?>

<? if ($jenkins === null) : ?>
    <? if (Leeroy\Perm::has('config', $seminar_id)) : ?>
        <?= MessageBox::info(sprintf(_('Sie haben noch keine Konfiguration für das Backend angelegt!') . ' %s' . _('Konfiguration anlegen.') . '%s', '<a href="' . $controller->url_for('dozent/config_jenkins') . '">', '</a>')); ?>
    <? else : ?>
        <?= MessageBox::info(_('Die Konfiguration ist noch nicht abgeschlossen!')) ?>
    <? endif ?>
    <br><br><br><br><br><br><br>
<? elseif (is_bool($aux) && !$aux) : ?>
    <?= MessageBox::error(_('Bitte füllen Sie ihre Zusatzangaben zuerst aus, vorher sind keine Abgaben erlaubt. ') . sprintf('%s%s%s', '<a href="' . URLHelper::getLink("teilnehmer_aux.php") . '">', 'Zusatzangaben ändern', '</a>')); ?>
    <? #TODO Link ?>

    <table class="default zebra">
        <thead>
        <tr>
            <th style="min-width: 200px"><?= _('Feld') ?></th>
            <th style="min-width: 200px"><?= _('Ihre Eingabe') ?></th>
            <th style="width: 100%"><?= _('Geforderter Regex') ?></th>
            <th style="min-width: 200px"><?= _('Status') ?></th>
        </tr>
        </thead>
        <?
        #foreach($this->data["aux"][$user_id]["entry"] as $field => $value) {
        #    $ret = $ret && @preg_match('^'.$regex->$field.'$', $value);
        #}
        ?>
        <? foreach ($aux_user as $field => $value) : ?>
            <tr>
                <td>
                    <?= $aux_headers[$field] ?>
                </td>

                <td>
                    <?= $value ?>
                </td>

                <td>
                    <?= '^' . $aux_regex->$field . '$' ?>
                </td>

                <td>
                    <?= preg_match('/^' . $aux_regex->$field . '$/', $value) ? _('akzeptiert') : _('NICHT akzeptiert') ?>
                </td>
            </tr>
        <? endforeach ?>
        <tbody>
        </tbody>
    </table>
<? elseif (!Leeroy\Perm::has('new_task', $seminar_id) && count(GetGroupsByCourseAndUser($seminar_id, $GLOBALS['user']->id)) === 0) : ?>
    <?= MessageBox::error(_('Sie sind keiner Übungsgruppe zugeordnet, Bitte treten Sie zuerst einer Gruppe bei. ') . sprintf('%s%s%s', '<a href="' . URLHelper::getLink("statusgruppen.php") . '">', 'Einer Gruppe beitreten', '</a>')); ?>
<? else : ?>
    <? if (count($tasks) === 0) : ?>
        <? if (Leeroy\Perm::has('new_task', $seminar_id)) : ?>
            <?= MessageBox::info(sprintf(_('Sie haben noch keine Aufgaben angelegt') . '%s' . _('Neue Aufgabe anlegen.') . '%s',
                '<a href="' . $controller->url_for('dozent/new_task') . '">', '</a>')); ?>
        <? else : ?>
            <?= MessageBox::info(_('Es sind noch keine Aufgaben sichtbar/vorhanden')) ?>
        <? endif ?>
        <br><br><br><br><br><br><br>
    <? else : ?>
        <?= $this->render_partial('index/_breadcrumb', array('path' => array('overview'))) ?>
        <h2><?= _('Aufgaben') ?></h2>
        <? if (Leeroy\Perm::has('new_task', $seminar_id)) : ?>
            <?= $this->render_partial('dozent/_index_dozent'); ?>
            <?= Studip\LinkButton::create(_('Neue Aufgabe anlegen'), $controller->url_for('dozent/new_task')) ?>
        <? else : ?>
            <?= $this->render_partial('student/_index_autor'); ?>
        <? endif; ?>
    <? endif ?>
<? endif ?>
