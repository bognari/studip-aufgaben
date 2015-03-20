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

$this->render_partial('student/stow_analytics', compact('task', 'files', 'data'));

/*$content = array(array(
    'kategorie' => _('Aktionen'),
    'eintrag' => array()));

$infobox = array('picture' => 'infobox/schedules.jpg', 'content' => $content);
*/ ?><!--

<? /*= $this->render_partial('index/_breadcrumb', array('path' => array('overview', 'Analyseergebnisse für ' . $task->title))) */ ?>

    <br>
    <h1> <? /*= _('Analyseergebnisse für') */ ?>  <? /*= htmlReady($task->title) */ ?> : </h1>

<? /* if (count($files) > 0) : */ ?>
    <? /* foreach ($files as $name => $file) : */ ?>
        <br>
        <br>
        <h2> <? /*= _('Datei') . ':' */ ?>  <? /*= htmlReady($name) */ ?> : </h2>
        <table class="default zebra">
            <thead>
            <tr>
                <th style="min-width: 50px"><? /*= _('Zeile') */ ?></th>
                <th style="width: 100%"><? /*= _('Fehler') */ ?></th>
            </tr>
            </thead>
            <? /* foreach ($file as $warning) : */ ?>
                <tr>
                    <td>
                        <? /*= htmlReady($warning->primaryLineNumber) */ ?>
                    </td>

                    <td>
                        <? /*= htmlReady($warning->message) */ ?>
                    </td>
                </tr>
            <? /* endforeach */ ?>
            <tbody>
            </tbody>
        </table>
    <? /* endforeach */ ?>
<? /* elseif ($data->analytic === 'fail') : */ ?>
    <? /*= MessageBox::error(_('Analyse fehlgeschlagen.')); */ ?>
<? /* elseif ($data->analytic === null) : */ ?>
    <? /*= MessageBox::error(_('Keine Analyse ausgeführt.')); */ ?>
<? /* else : */ ?>
    <? /*= MessageBox::info(_('Die Analyse konnte keine Fehler finden :)')); */ ?>
--><? /* endif */ ?>
