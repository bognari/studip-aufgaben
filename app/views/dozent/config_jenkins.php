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

$infobox_entrys[] = array();
$i = 0;

if ($jenkins->use_jenkins && $connected) :
    $infobox_entrys[$i++] = array(
        'icon' => 'icons/16/black/link-extern.png',
        'text' => sprintf(_('%sJenkins%s'), '<a target="_blank" href="' . $jenkins->getURL() . '">', '</a>')
    );
endif;

$infobox_content[] = array(
    'kategorie' => _('Aktionen'),
    'eintrag' => $infobox_entrys
);


$infobox = array('picture' => 'infobox/schedules.jpg', 'content' => $infobox_content);
?>

<? if ($jenkins->use_jenkins) : ?>
    <? if ($connected) : ?>
        <?= MessageBox::success(_('Zugangsdaten für Jenkins sind richtig')); ?>
    <? else : ?>
        <?= MessageBox::error(_('Zugangsdaten für Jenkins sind falsch')) ?>
    <? endif ?>
<? endif ?>

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', "Jenkins"))) ?>

<h2><?= _('Jenkins bearbeiten') ?></h2>

<form action="<?= $controller->url_for('dozent/config_jenkins_save/' . $seminar_id) ?>" method="post">
    <div class="task">

        <label>
            <input type="checkbox" name="use_jenkins" value="1" <?= $jenkins->use_jenkins ? 'checked="checked"' : '' ?>>
            <?= _('Jenkins wird als Backend benutzt') ?>
        </label>
        <br>

        <span class="label"><?= _('Jenkins URL (ohne http[s]://)') ?></span>
        <input type="text" name="url" value="<?= htmlReady($jenkins->jenkins_url) ?>" size="40"><br>
        <br>
        <span class="label"><?= _('Jenkins Benutzer') ?></span>
        <input type="text" name="user" value="<?= htmlReady($jenkins->jenkins_user) ?>" size="40"><br>
        <br>
        <span class="label"><?= _('Jenkins Token') ?></span>
        <input type="text" name="token" size="40"><br>
        <br>

        <label>
            <input type="checkbox" name="use_ssl" value="1" <?= $jenkins->use_ssl ? 'checked="checked"' : '' ?>>
            <?= _('Benutze https statt http') ?>
        </label>
        <br>

    </div>

    <br style="clear: both">

    <div class="buttons">
        <div class="button-group">
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('dozent/config_jenkins/' . $seminar_id)) ?>
        </div>
    </div>
</form>