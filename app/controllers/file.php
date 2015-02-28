<?php
/**
 * FileController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      Marcus Lunzenauer <mlunzena@uos.de>
 * @author      Stephan Mielke
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
require_once 'Leeroy_Controller.php';

require_once $this->trails_root . '/models/Jenkins.php';
require_once $this->trails_root . '/models/Tasks.php';
require_once $this->trails_root . '/models/TaskFiles.php';
require_once $this->trails_root . '/models/Handin.php';
require_once $this->trails_root . '/models/HandinFiles.php';
require_once $this->trails_root . '/models/Perm.php';
require_once $this->trails_root . '/models/Job.php';
require_once $this->trails_root . '/models/JobBuild.php';
require_once $this->trails_root . '/models/TimeTrigger.php';
require_once $this->trails_root . '/models/DataFields.php';

class FileController extends LeeroyStudipController
{
    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // set default layout
        $this->set_layout('layouts/layout');

        $nav = Navigation::getItem('course/leeroy');
        $nav->setImage('icons/16/black/assessment.png');
        Navigation::activateItem('course/leeroy');
    }

    function handin_file_remove_action($file_id)
    {
        $file = new Leeroy\HandinFiles($file_id);

        if (($file->handin->task->startdate > time() || $file->handin->task->enddate < time())
            && !$GLOBALS['perm']->have_studip_perm('dozent', $this->seminar_id)
        ) {
            throw new AccessDeniedException(_('Sie dürfen diese Aufgabe nicht bearbeiten!'));
        }

        // only delete file, if it belongs to the current user
        if ($file->document->user_id === $GLOBALS['user']->id) {

            if ($file->type === 'answer') {
                $file->handin->analytic = null;
                $file->handin->test = null;
                $file->handin->link = null;
                $file->handin->lastJob = null;
                $file->handin->log = null;
                $file->handin->store();
            }

            delete_document($file->document->getId());
            $file->delete();
        } else {
            throw new AccessDeniedException(_('Diese Datei gehört nicht Ihnen!'));
        }

        $this->render_nothing(array('status' => 'success'));
    }

    function handin_file_add_action($handin_id)
    {
        $handin = new Leeroy\Handin($handin_id);
        $task = new Leeroy\Tasks($handin->task_id);

        if (($task->startdate > time() || $task->enddate < time()) && !$GLOBALS['perm']->have_studip_perm('dozent', $this->seminar_id)) {
            throw new AccessDeniedException(_('Sie dürfen diese Aufgabe nicht bearbeiten!'));
        }

        if ($task->seminar_id !== $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        // user adds file(s) to its solution of the task
        if ($handin->user_id === $GLOBALS['user']->id && $GLOBALS['perm']->have_studip_perm('autor', $this->seminar_id)) {
            $type = 'answer';
        } elseif ($GLOBALS['perm']->have_studip_perm('dozent', $this->seminar_id)) {    // dozent adds feedback for the user
            $type = 'feedback';
        } else { // not author/tutor nor dozent, so access is denied
            throw new AccessDeniedException(_('Sie haben keine Rechte zum Bearbeiten dieser Aufgabe'));
        }

        if (!Request::isPost() || !$GLOBALS['perm']->have_studip_perm('autor', $this->seminar_id)
        ) {
            throw new AccessDeniedException('Kein Zugriff');
        }

        $files = $this->save_files($type);

        $output = array();

        foreach ($files as $file) {
            $GLOBALS['msg'] = '';

            if ($GLOBALS['msg']) {
                $output['errors'][] = $file['name'] . ': ' . studip_utf8encode(decodeHTML(trim(substr($GLOBALS['msg'], 6, -1), '?')));
                continue;
            }

            if (!is_null($file)) {

                if ($type === 'answer' && $handin->getFileAnswer()->handin->id === $handin_id) { # nur eine abgabe ist erlaubt
                    throw new AccessDeniedException(_('Nur eine Abgabe ist erlaubt'));
                }

                $data = array(
                    'handin_id' => $handin_id,
                    'dokument_id' => $file->id,
                    'type' => $type
                );

                $handin_file = Leeroy\HandinFiles::create($data);

                if ($handin_file->type === 'answer') {

                    $handin->analytic = null;
                    $handin->test = null;
                    $handin->link = null;
                    $handin->lastJob = null;
                    $handin->log = null;
                    $handin->store();

                    foreach ($task->jobs as $job) { # trigger
                        if ($job->trigger === 'upload') {
                            $job->execute(get_upload_file_path($file->getId()), $this->getPluginURL(), $handin_file->id);
                        }
                    }
                }

                $output[] = array(
                    'url' => GetDownloadLink($file->getId(), $file['filename']),
                    'id' => $handin_file->getId(),
                    'name' => $file->name,
                    'date' => strftime($this->timeformat, time()),
                    'size' => $file->filesize,
                    'seminar_id' => $this->seminar_id
                );
            }
        }

        $this->render_json($output);
    }

    function task_file_remove_action($file_id)
    {
        Leeroy\Perm::check('new_task', $this->seminar_id);

        $file = new Leeroy\TaskFiles($file_id);
        if ($file->task->seminar_id === $this->seminar_id) {
            $file = new Leeroy\TaskFiles($file_id);
            $document = new Leeroy_StudipDocument($file_id);

            delete_document($file->document->getId());
            $file->delete();
            $document->delete();
        } else {
            throw new AccessDeniedException(_('Die Datei wurde nicht gefunden!'));
        }

        $this->render_json(array('status' => 'success'));
    }

    function task_file_add_action($task_id)
    {
        Leeroy\Perm::check('new_task', $this->seminar_id);

        $task = new Leeroy\Tasks($task_id);

        if ($task->seminar_id !== $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        $files = $this->save_files('Material');

        $output = array();

        foreach ($files as $file) {
            $GLOBALS['msg'] = '';

            if ($GLOBALS['msg']) {
                $output['errors'][] = $file['name'] . ': ' . studip_utf8encode(decodeHTML(trim(substr($GLOBALS['msg'], 6, -1), '?')));
                continue;
            }

            if (!is_null($file)) {
                $data = array(
                    'task_id' => $task->id,
                    'dokument_id' => $file->getId()
                );

                $task_file = Leeroy\TaskFiles::create($data);

                $output[] = array(
                    'url' => GetDownloadLink($file->getId(), $file['filename']),
                    'id' => $task_file->getId(),
                    'name' => $file->name,
                    'date' => strftime($this->timeformat, time()),
                    'size' => $file->filesize,
                    'seminar_id' => $this->seminar_id
                );
            }
        }
        $this->render_json($output);
    }
}