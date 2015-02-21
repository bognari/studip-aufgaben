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

# http://www.phpliveregex.com/
$infobox_entrys[] = array();


$i = 0;
$infobox_entrys[$i++] = array(
    'icon' => 'icons/16/black/link-extern.png',
    'text' => sprintf('%s' . _('PHP Regex Hilfe') . '%s', '<a target="_blank" href="http://www.phpliveregex.com/">', '</a>')
);

$infobox_content[] = array(
    'kategorie' => _('Aktionen'),
    'eintrag' => $infobox_entrys
);


$infobox = array('picture' => 'infobox/schedules.jpg', 'content' => $infobox_content);
#var_dump($regex);
?>

<? if (empty($headers)) : ?>
    <?= MessageBox::info(_('Es sind keine Zusatzdaten für diese Veranstaltung vorhanden.')); ?>
<? endif ?>

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', "Zusatzdaten"))) ?>

<h2><?= _('Zusatzdaten bearbeiten') ?></h2>

<form action="<?= $controller->url_for('dozent/config_aux_save/' . $seminar_id) ?>" method="post">
    <div class="task">

        <? #print_r($regex); die(); ?>

        <? foreach ($headers as $id => $name) : ?>
            <span class="label"><?= _($name . ':') ?></span>
            <input type="text" name="<?= _($id) ?>" required
                   value="<?= strlen($regex->$id) < 1 ? htmlReady("(.*)") : htmlReady($regex->$id) ?>" size="40"><br>
            <br>
        <? endforeach ?>

        <br>

        <label>
            <input type="checkbox" name="force_data" value="1" <?= $jenkins->force_data ? 'checked="checked"' : '' ?>>
            <?= _('Erzwinge Zusatzangaben') ?>
        </label>

        <br style="clear: both">
    </div>

    <div class="buttons">
        <div class="button-group">
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('dozent/config_aux/' . $seminar_id)) ?>
        </div>
    </div>
</form>