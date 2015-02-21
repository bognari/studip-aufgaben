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
<a name="jumpto_<?= $field ?>"></a>

<? if ($edit[$field]) : ?>

    <form action="<?= $controller->url_for($form_route) ?>" method="post">
        <b><?= $name ?>:</b><br>
        <br>
        <textarea name="<?= $field ?>" class="add_toolbar"
                  style="width: 100%; height: 400px;"><?= htmlReady($text) ?></textarea>
        <br>

        <div class="buttons">
            <div class="button-group">
                <?= Studip\Button::createAccept(_('Speichern')) ?>
                <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for($cancel_route . '#jumpto_' . $field)) ?>
            </div>
        </div>
    </form>

<? else : ?>

    <b><?= $name ?>:</b><br>
    <? if ($text) : ?>
        <br>
        <?= formatReady($text) ?><br>
    <? else : ?>
        <span class="empty_text"><?= _('Es wurde noch kein Text eingegeben') ?></span>
    <? endif ?>

    <div class="buttons">
        <div class="button-group">
            <?= Studip\LinkButton::createEdit(_('Bearbeiten'), $controller->url_for($cancel_route . '/' . $field . '#jumpto_' . $field)) ?>
            <? /* <?= $delete_route ? \Studip\LinkButton::createDelete(_('L?schen'), $controller->url_for($delete)) : '' */ ?>
        </div>
    </div>

<? endif; ?>
