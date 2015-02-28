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
<!-- multi file upload -->
<?
$art = $GLOBALS['SessSemName']['art_num'];
if (!$GLOBALS['UPLOAD_TYPES'][$art]) {
    $art = 'default';
}

$max = $GLOBALS['UPLOAD_TYPES'][$art]['file_sizes'][$GLOBALS['perm']->get_studip_perm($GLOBALS['SessSemName'][1])]
?>

<script>
    STUDIP.Leeroy.maxFilesize = <?= $max ?>;
    <? if (is_numeric($max_file)) : ?>
    STUDIP.Leeroy.maxFiles = "<?= $max_file - count($files) ?>";
    <? endif ?>
    <? if (is_bool($open_analytic) && $open_analytic) : ?>
    STUDIP.Leeroy.openAnalytic = true;
    STUDIP.Leeroy.handin = <?= $id ?>;
    <? endif ?>
</script>

<div style="position: relative; display: inline-block;">
    <a id="add_button"
       class="button <?= (is_numeric($max_file) && ($max_file - count($files)) <= 0) ? ' disabled' : '' ?>"
       style="overflow: hidden; position: relative;">
        <?= _('Datei(en) hinzufügen') ?>
        <input id="fileupload" type="file" <?= $max_file === null ? 'multiple' : '' ?> name="file"
               data-url="<?= $controller->url_for($url . '_add/' . $id . '/' . $type) ?>"
               data-sequential-uploads="true"
               style="opacity: 0; position: absolute; left: -2px; top: -2px; height: 105%; cursor: pointer;" <?= (is_numeric($max_file) && ($max_file - count($files)) <= 0) ? 'disabled' : '' ?>>
    </a>
</div>

<?= Studip\LinkButton::create(_('Datei(en) hochladen'), 'javascript:STUDIP.Leeroy.upload()',
    array('id' => 'upload_button', 'class' => 'disabled')) ?>

<b><?= _('Maximal erlaubte Größe pro Datei') ?>: <?= round($max / 1024 / 1024, 2) ?> MB</b><br>
<? #TODO anzahl von Dateien ?>
<table class="default zebra">
    <tbody id="files_to_upload">

    </tbody>
</table>