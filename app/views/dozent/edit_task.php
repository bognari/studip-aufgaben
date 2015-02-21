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

#var_dump($task);

$infobox_entrys[] = array();
$i = 0;

if ($jenkins->use_jenkins && $connected) :
    $infobox_entrys[$i++] = array(
        'icon' => 'icons/16/black/link-extern.png',
        'text' => sprintf('%s' . _('Jenkins') . '%s', '<a target="_blank" href="' . $jenkins->getURL() . '">', '</a>')
    );
endif;

$infobox_content[] = array(
    'kategorie' => _('Aktionen'),
    'eintrag' => $infobox_entrys
);


$infobox = array('picture' => 'infobox/schedules.jpg', 'content' => $infobox_content);

?>

<? if (!$task->is_active) : ?>
    <?= MessageBox::info(_('Die Aufgabe ist nicht freigeschaltet!')); ?>
<? endif ?>

<? if (!$connected) : ?>
    <?= MessageBox::error(_('Jenkins ist falsch Konfiguriert!')); ?>
<? else : ?>
    <? foreach ($task->jobs as $job) : ?>
        <? if (!$job->isValid()) : ?>
            <?= MessageBox::error(_('Job ') . $job->name . _(' ist falsch Konfiguriert!')); ?>
        <? endif ?>
    <? endforeach ?>
<? endif ?>

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', $task['title']))) ?>

<h2><?= _('Aufgabe bearbeiten') ?></h2>

<form action="<?= $controller->url_for('dozent/update_task/' . $task['id']) ?>" method="post"
      enctype="multipart/form-data">

    <div class="task">

        <h3><span class="label"><?= _('Allgemeine Einstellungen') ?></span></h3>

        <div>
            <span class="label"><?= _('Titel') ?></span>
            <input type="text" name="title" placeholder="Name der Aufgabe" required
                   value="<?= htmlReady($task['title']) ?>" size="80"><br>
            <span class="label"><?= _('Externer Link') ?></span>
            <input type="text" name="task_link" placeholder="Link zu weiteren Angaben zur Aufgabe"
                   value="<?= htmlReady($task['task_link']) ?>" size="80"><br>
            <label>
                <span class="label"><?= _('Pflichtaufgabe: ') ?> <input type="checkbox" name="required"
                                                                        value="1" <?= $task['required'] ? 'checked="checked"' : '' ?>></span>
            </label>
            <br>
            <span class="label"><?= _('Beschreibung') ?></span>
            <textarea name="content"><?= htmlReady($task['content']) ?></textarea>
        </div>
        <br>

        <h3><span class="label"><?= _('Abgabeformate') ?></span></h3>

        <div>
            <label>
                <input type="checkbox" name="allow_text"
                       value="1" <?= $task['allow_text'] ? 'checked="checked"' : '' ?>>
                <?= _('Texteingabe erlauben') ?>
            </label>

            <label>
                <input type="checkbox" name="allow_files"
                       value="1" <?= $task['allow_files'] ? 'checked="checked"' : '' ?>>
                <?= _('Dateiupload erlauben') ?>
            </label>
        </div>
        <br>

        <h3><span class="label"><?= _('Abgabezeitraum') ?></span></h3>
        <div>
            <?= _('Sichtbar und bearbeitbar ab') ?>:<br>
            <input type="datetime" name="startdate" placeholder="<?= _('tt.mm.jjjj ss:mm') ?>" required
                   value="<?= $task['startdate'] != '' ? strftime('%d.%m.%Y %R', $task['startdate']) : "" ?>">
        </div>
        <div>
            <?= _('Bearbeitbar bis') ?>:<br>
            <input type="datetime" name="enddate" placeholder="<?= _('tt.mm.jjjj ss:mm') ?>" required
                   value="<?= $task['enddate'] != '' ? strftime('%d.%m.%Y %R', $task['enddate']) : "" ?>">
        </div>
        <!--</div>-->
        <br>

        <h3><span class="label"><?= _('Materialien') ?></span></h3>

        <div>
            <?= $this->render_partial('index/_file_list', array(
                'files' => $task->files,
                'edit' => true,
                'type' => 'material',
                'url' => 'file/task_file',
                'id' => $task->id
            )) ?>
        </div>
        <? if ($jenkins->use_jenkins) : ?>

            <script>
                $(function () {

                    var count = <?= count($task->jobs) - 1; ?>

                        $('a#add_job').click(function () {
                            count += 1;
                            var ids = $('#max_jobs').val() + ' ' + count;

                            $('#max_jobs').val(ids.trim());
                            $('<div id="' + count + '" class="ui-widget ui-widget-content">' +
                            '<p>' +
                            '<input type="hidden" name="<?= _("job_id")?>' + count + '" value="new">' +
                            '<span class="label"><?= _('Job Name:') ?></span>' +
                            '<input type="text" name="<?= _("job_name")?>' + count + '" required placeholder=" <?= _('Name des Jobs im Jenkins') ?>" size="80"><br>' +
                            '<span class="label"><?= _('Beschreibung:') ?></span>' +
                            '<textarea name="<?= _("job_description")?>' + count + '"></textarea><br><br>' +
                            '<span class="label"><?= _('Konfigurations Datei:') ?><input type="file" name="<?= _("job_config")?>' + count + '"></span>' +
                            '<br>' +
                            '<span class="label"><?= _('Benutze Konfigurations Datei:') ?>' +
                            '<input type="checkbox" name="<?= _("job_use_config_file")?>' + count + '" checked="checked"> </span>' +
                            '<br>' +
                            '<span class="label"><?= _('Trigger:') # laden der speicherung?>' +
                            '<select name="<?= _("job_trigger")?>' + count + '" ?>" size="3">' +
                            '<option>upload</option>' +
                            '<option>end</option>' +
                            '<option>end_all</option>' +
                            '</select>' +
                            '</span>' +
                            '<a href="#" class="remove" id="remove_job" title="remove job"><?= Assets::img('icons/16/black/remove.png', array('title' => _('entfernen'))) ?></a>' +
                            '</p>' +
                            '</div>').fadeIn("slow").appendTo('#extend_job');
                            return false;
                        });


                    //fadeout selected item and remove
                    $('.remove').live('click', function () {
                        $(this).parent().parent().fadeOut(300, function () {
                            var id = $(this).attr('id');
                            var ids = $('#max_jobs').val();
                            var p = "\\s*" + id + "\\s*";
                            var r = new RegExp(p, "g");
                            ids = ids.replace(r, ' ');
                            ids = ids.trim();
                            $('#max_jobs').val(ids);
                            $(this).remove();
                            return false;
                        });
                    });

                });
            </script>


            <div class="ui-widget">
                <h2>Jenkins</h2>

                <?
                $ids = "";
                $j = 0;

                foreach ($task->jobs as $job) {
                    $ids = $ids . $j . ' ';
                    $j += 1;
                }
                $ids = trim($ids);
                ?>

                <input id="max_jobs" type="hidden" name="max_jobs" value="<?= $ids ?> ">

                <div id="extend_job" class="ui-widget ui-widget-content">
                    <? $i = 0;
                    foreach ($task->jobs as $job) : ?>
                        <div id="<?= _($i) ?>" class="ui-widget ui-widget-content" style="display: block;">
                            <p>
                                <input type="hidden" name="<?= _("job_id") . $i ?>" value="<?= htmlReady($job->id) ?>">
                                <span class="label"><?= _('Job Name:') ?></span>
                                <input type="text" name="<?= _("job_name") . $i ?>" value="<?= htmlReady($job->name) ?>"
                                       required placeholder="<= _('Name des Jobs im Jenkins') ?>" size="80"><br>
                                <span class="label"><?= _('Beschreibung:') ?></span>
                                <textarea
                                    name="<?= _("job_description") . $i ?>"><?= htmlReady($job->description) ?></textarea><br><br>
                                <span class="label"><?= _('Konfigurations Datei:') ?><input type="file"
                                                                                            name="<?= _("job_config") . $i ?>"></span>
                                <br>
                                <span class="label"><?= _('Benutze Konfigurations Datei:') ?>
                                    <? if (!is_null($job->file->name)) : ?>
                                        <?= htmlReady($job->file->name) ?>
                                    <? endif ?>
                                    <input type="checkbox" name="<?= _("job_use_config_file") . $i ?>"
                                           value="1" <?= is_null($job->file->name) ? '' : 'checked="checked"' ?>> </span>
                                <br>
                                <span class="label"><?= _('Trigger:') # laden der speicherung         ?>
                                    <select name="<?= _("job_trigger") . $i ?>" ?>" size="3">
                                        <option <?= $job->trigger == "upload" ? htmlReady('selected="selected"') : '' ?>>
                                            upload
                                        </option>
                                        <option <?= $job->trigger == "end" ? htmlReady('selected="selected"') : '' ?>>
                                            end
                                        </option>
                                        <option <?= $job->trigger == "end_all" ? htmlReady('selected="selected"') : '' ?>>
                                            end_all
                                        </option>
                                    </select>
                                </span>
                                <a href="#" class="remove" id="remove_job"
                                   title="remove job"><?= Assets::img('icons/16/black/remove.png', array('title' => _('entfernen'))) ?></a>
                            </p>
                        </div>
                        <? $i++;
                    endforeach ?>
                </div>
                <a href="#" id="add_job"
                   title="add job"><?= Assets::img('icons/16/black/add.png', array('title' => _('hinzufügen'))) ?></a>
            </div>

        <? endif ?>
    </div>

    <br style="clear: both">

    <? /*
    <label>
        <input type="checkbox" name="send_mail" value="1" <?= $task['send_mail'] ? 'checked="checked"' : '' ?>>
        <?= _('Mail an alle sobald sichtbar') ?>
    </label>
    */ ?>

    <div class="buttons">
        <div class="button-group">
            <div class="visibility">
                <label>
                    <input type="checkbox" name="is_active"
                           value="1" <?= $task['is_active'] ? 'checked="checked"' : '' ?>>
                    <?= _('Aufgabe freischalten') ?>
                </label>
            </div>
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('dozent/view_task/' . $task['id'])) ?>
        </div>
    </div>
</form>

<script>
    $(document).ready(function () {
        if (typeof Modernizr === 'undefined' || !Modernizr.inputtypes.datetime) {
            $('input[type=datetime]').datetimepicker();
        }
    });
</script>
