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
 * @author      Till Gl�ggler <tgloeggl@uos.de>
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

$infobox_content[] = array(
    'kategorie' => _('Trigger'),
    'eintrag' => array(
        array(
            'icon' => 'icons/16/black/infopage.png',
            'text' => _('"upload": analysiert direkt nach dem Hochladen')
        ),
        array(
            'icon' => 'icons/16/black/infopage.png',
            'text' => _('"end": analysiert nach dem Ende des Bearbeitungszeitraums')
        ),
        array(
            'icon' => 'icons/16/black/infopage.png',
            'text' => _('"end_all": analysiert alle Abgaben zusammen nach dem Ende des Bearbeitungszeitraums')
        )
    )
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

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', array('dozent/view_task/' . $task->getId(), $task['title']), array('dozent/edit_task/' . $task->getId(), _('Bearbeiten'))))) ?>

<h2><?= _('Aufgabe bearbeiten') ?></h2>

<form action="<?= $controller->url_for('dozent/update_task/' . $task['id']) ?>" method="post"
      enctype="multipart/form-data">

    <div class="task">

        <h3><span class="label"><?= _('Allgemeine Einstellungen') ?></span></h3>

        <div>
            <label for="title"><span class="label"><?= _('Titel') . ': ' ?></span></label>
            <input type="text" name="title" id="title" placeholder="Name der Aufgabe" required
                   value="<?= htmlReady($task['title']) ?>" size="80"><br>
            <label for="task_link"><span class="label"><?= _('Externer Link') . ': ' ?></span></label>
            <input type="text" name="task_link" id="task_link" placeholder="Link zu weiteren Angaben zur Aufgabe"
                   value="<?= htmlReady($task['task_link']) ?>" size="80"><br>

            <label for="task_points"><span class="label"><?= _('Punktebegrenzung') . ': ' ?></span></label>
            <input type="text" name="lower_bound_points" id="task_points" placeholder="Untere Grenze"
                   value="<?= htmlReady($task['lower_bound_points']) ?>" size="20">
            <input type="text" name="upper_bound_points" id="task_points" placeholder="Obere Grenze"
                   value="<?= htmlReady($task['upper_bound_points']) ?>" size="20"><br>

            <label><span class="label"><?= _('Pflichtaufgabe') . ': ' ?> <input type="checkbox" name="required"
                                                                                value="1" <?= $task['required'] ? 'checked="checked"' : '' ?>></span></label>
            <br>
            <label for="content"><span class="label"><?= _('Beschreibung') ?></span></label>
            <textarea name="content" id="content"><?= htmlReady($task['content']) ?></textarea>
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
            <label for="startdate"><?= _('Sichtbar und bearbeitbar ab') ?>:</label><br>
            <input type="datetime" name="startdate" id="startdate" placeholder="<?= _('tt.mm.jjjj ss:mm') ?>" required
                   value="<?= $task['startdate'] !== '' ? strftime('%d.%m.%Y %R', $task['startdate']) : '' ?>">
        </div>
        <div>
            <label for="enddate"><?= _('Bearbeitbar bis') ?>:</label><br>
            <input type="datetime" name="enddate" id="enddate" placeholder="<?= _('tt.mm.jjjj ss:mm') ?>" required
                   value="<?= $task['enddate'] !== '' ? strftime('%d.%m.%Y %R', $task['enddate']) : '' ?>">
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
                            '<input type="hidden" name="<?= 'job_id'?>' + count + '" value="new">' +
                            '<label for="<?= 'job_name'?>' + count + '"><span class="label"><?= _('Jobname:') ?></span></label>' +
                            '<input type="text" name="<?= 'job_name'?>' + count + '" id="<?= 'job_name'?>' + count + '" required placeholder=" <?= _('Name des Jobs im Jenkins') ?>" size="80"><br>' +
                            '<label for="<?= 'job_description'?>' + count + '"><span class="label"><?= _('Beschreibung:') ?></span></label>' +
                            '<textarea name="<?= 'job_description'?>' + count + '" id="<?= 'job_description'?>' + count + '"></textarea><br><br>' +
                            '<label for="<?= 'job_config'?>' + count + '"><span class="label"><?= _('Konfigurationsdatei:') ?></span></label><input type="file" name="<?= 'job_config'?>' + count + '" id="<?= 'job_config'?>' + count + '">' +
                            '<br>' +
                            '<label for="<?= 'job_use_config_file'?>' + count + '"><span class="label"><?= _('Benutze Konfigurationsdatei:') ?>' +
                            '<input type="checkbox" name="<?= 'job_use_config_file'?>' + count + '" checked="checked" id="<?= 'job_use_config_file'?>' + count + '"> </span></label>' +
                            '<br>' +
                            '<label for="<?= 'job_trigger'?>' + count + '"><span class="label"><?= _('Trigger:') # laden der speicherung?>' +
                            '<select name="<?= 'job_trigger'?>' + count + '" size="1" id="<?= 'job_trigger'?>' + count + '">' +
                            '<option>upload</option>' +
                            '<option>end</option>' +
                            '<option>end_all</option>' +
                            '</select>' +
                            '</span></label>' +
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
                $ids = '';
                $j = 0;

                foreach ($task->jobs as $job) {
                    $ids = $ids . $j . ' ';
                    $j++;
                }
                $ids = trim($ids);
                ?>

                <input id="max_jobs" type="hidden" name="max_jobs" value="<?= $ids ?> ">

                <div id="extend_job" class="ui-widget ui-widget-content">
                    <? $i = 0;
                    foreach ($task->jobs as $job) : ?>
                        <div id="<?= _($i) ?>" class="ui-widget ui-widget-content" style="display: block;">
                            <p>
                                <input type="hidden" name="<?= 'job_id' . $i ?>" value="<?= htmlReady($job->id) ?>">
                                <label for="<?= 'job_name' . $i ?>"><span
                                        class="label"><?= _('Jobname:') ?></span></label>
                                <input type="text" name="<?= 'job_name' . $i ?>" id="<?= 'job_name' . $i ?>"
                                       value="<?= htmlReady($job->name) ?>"
                                       required placeholder="<= _('Name des Jobs im Jenkins') ?>" size="80"><br>
                                <label for="<?= 'job_description' . $i ?>"><span
                                        class="label"><?= _('Beschreibung:') ?></span></label>
                                <textarea
                                    name="<?= 'job_description' . $i ?>"
                                    id="<?= 'job_description' . $i ?>"><?= htmlReady($job->description) ?></textarea><br><br>
                                <label for="<?= 'job_config' . $i ?>"><span
                                        class="label"><?= _('Konfigurationsdatei:') ?></span></label><input type="file"
                                                                                                             name="<?= 'job_config' . $i ?>"
                                                                                                             id="<?= 'job_config' . $i ?>">
                                <br>
                                <label for="<?= 'job_use_config_file' . $i ?>"><span
                                        class="label"><?= _('Benutze Konfigurationsdatei:') ?>
                                        <? if ($job->file->name !== null) : ?>
                                        <?= htmlReady($job->file->name) ?>
                                    <? endif ?>
                                        <input type="checkbox" name="<?= 'job_use_config_file' . $i ?>"
                                               id="<?= 'job_use_config_file' . $i ?>"
                                               value="1" <?= $job->file->name === null ? '' : 'checked="checked"' ?>> </span>
                                </label>
                                <br>
                                <label for="<?= 'job_trigger' . $i ?>"><span
                                        class="label"><?= _('Trigger:') # laden der speicherung          ?>
                                        <select name="<?= 'job_trigger' . $i ?>" id="<?= 'job_trigger' . $i ?>"
                                                size="1">
                                        <option <?= $job->trigger === 'upload' ? htmlReady('selected="selected"') : '' ?>>
                                            upload
                                        </option>
                                        <option <?= $job->trigger === 'end' ? htmlReady('selected="selected"') : '' ?>>
                                            end
                                        </option>
                                        <option <?= $job->trigger === 'end_all' ? htmlReady('selected="selected"') : '' ?>>
                                            end_all
                                        </option>
                                    </select>

                                </span></label>
                                <a href="#" class="remove" id="remove_job"
                                   title="remove job"><?= Assets::img('icons/16/black/remove.png', array('title' => _('entfernen'))) ?></a>
                            </p>
                        </div>
                        <? $i++;
                    endforeach ?>
                </div>
                <a href="#" id="add_job"
                   title="add job"><?= Assets::img('icons/16/black/add.png', array('title' => _('hinzuf�gen'))) ?></a>
            </div>

        <? endif ?>
    </div>

    <br style="clear: both">

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
