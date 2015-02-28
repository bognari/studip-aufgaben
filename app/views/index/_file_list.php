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
<!-- files already there -->

<? if ($edit) : ?>
    <script>
        STUDIP.Leeroy.remove_url = "<?= $url ?>";
    </script>
<? endif ?>

<table class="default zebra">
    <thead>
    <tr>
        <th style="width:60%"><?= _('Datei') ?></th>
        <th style="width:10%"><?= _('Größe') ?></th>
        <th style="width:20%"><?= _('Datum') ?></th>
        <? if ($edit) : ?>
            <th style="width:10%"><?= _('Aktionen') ?></th>
        <? endif ?>
    </tr>
    </thead>
    <tbody <?= $edit ? 'id="uploaded_files"' : '' ?>>
    <? if (count($files) > 0) foreach ($files as $file) : ?>
        <tr data-fileid="<?= $file->getId() ?>">
            <td>
                <a href="<?= GetDownloadLink($file->document->getId(), $file->document->name) ?>" target="_blank">
                    <?= $file->document->name ?>
                </a>
            </td>
            <td><?= round((($file->document->filesize / 1024) * 100) / 100, 2) ?> kb</td>
            <td><?= strftime($timeformat, $file->document->mkdate) ?></td>

            <? if ($edit) : ?>
                <td>
                    <? if ($GLOBALS['user']->id === $file->document->user_id || (Leeroy\Perm::has('new Task', $GLOBALS['user']) !== false)) : ?>
                        <a href="javascript:STUDIP.Leeroy.removeFile('<?= $seminar_id ?>', '<?= $file->getId() ?>')">
                            <?= Assets::img('icons/16/blue/trash.png') ?>
                        </a>
                    <? endif ?>
                </td>
            <? endif ?>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
<br>

<? if ($edit) : ?>
    <?= $this->render_partial('index/_file_upload', compact('type', 'url', 'files', 'id', 'open_analytic')) ?>
<? endif ?>
