<?php
/**
 * DozentController
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
use Leeroy\HandinFiles;
use Leeroy\Jenkins;
use Leeroy\Job;
use Leeroy\TimeTrigger;

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

class DozentController extends LeeroyStudipController
{
    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // set default layout
        $this->set_layout('layouts/layout');

        $nav = Navigation::getItem('course/leeroy');
        $nav->setImage('icons/16/black/assessment.png');
        Navigation::activateItem('course/leeroy');

        Leeroy\Perm::check('new_task', $this->seminar_id);
    }

    function new_task_action()
    {
        $this->jenkins = Leeroy\Jenkins::find($this->seminar_id);
        $this->connected = $this->jenkins->isConnected();

        $this->redirect('dozent/add_task');
    }

    function config_jenkins_action()
    {
        Leeroy\Perm::check('config', $this->seminar_id);

        $this->jenkins = Leeroy\Jenkins::find($this->seminar_id);
        if ($this->jenkins != null) {
            $this->connected = $this->jenkins->isConnected();
        }
    }

    function config_jenkins_save_action()
    {
        Leeroy\Perm::check('config', $this->seminar_id);

        $data = array(
            'seminar_id' => $this->seminar_id,
            'jenkins_url' => Request::get('url'),
            'jenkins_user' => Request::get('user'),
            'use_ssl' => Request::int('use_ssl'),
            'use_jenkins' => Request::int('use_jenkins'),
        );

        $token = Request::get('token');

        if (strlen($token) > 0) { // setzte das token nur wen eins angegeben wurde
            $data['jenkins_token'] = $token;
        }

        if (Jenkins::exists($this->seminar_id)) {
            $this->jenkins = new Jenkins($this->seminar_id);
            $this->jenkins->setData($data);
            $this->jenkins->store();
        } else {
            $this->jenkins = Jenkins::create($data);
        }

        $this->redirect('dozent/config_jenkins');
    }

    function config_aux_action()
    {
        Leeroy\Perm::check('config', $this->seminar_id);

        $this->jenkins = Leeroy\Jenkins::find($this->seminar_id);
        $this->headers = Leeroy\DataFields::getDataFields($this->seminar_id)->getHeaders();
        $this->regex = json_decode($this->jenkins->aux);
    }

    function config_aux_save_action()
    {
        Leeroy\Perm::check('config', $this->seminar_id);

        $headers = Leeroy\DataFields::getDataFields($this->seminar_id)->getHeaders();
        $regex = array();
        foreach ($headers as $id => $name) {
            $regex[$id] = Request::get($id);
        }
        $data = array(
            'seminar_id' => $this->seminar_id,
            'force_data' => Request::get('force_data'),
            'aux' => json_encode($regex),
        );
        if (Jenkins::exists($this->seminar_id)) {
            $this->jenkins = new Jenkins($this->seminar_id);
            $this->jenkins->setData($data);
            $this->jenkins->store();
        } else {
            $this->jenkins = Jenkins::create($data);
        }

        $this->redirect('dozent/config_aux');
    }

    function add_task_action()
    {
        $data = array(
            'seminar_id' => $this->seminar_id,
            'title' => 'neue Aufgabe',
            'is_active' => false,
            'chdate' => time(),
            'mkdate' => time(),
        );

        $task = Leeroy\Tasks::create($data);
        $this->redirect('dozent/edit_task/' . $task->id);
    }

    function update_task_action($task_id)
    {
        $task = new Leeroy\Tasks($task_id);

        if ($task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        $data = array(
            'seminar_id' => $this->seminar_id,
            'chdate' => time(),
            'title' => Request::get('title'),
            'content' => Request::get('content'),
            'allow_text' => Request::int('allow_text'),
            'allow_files' => Request::int('allow_files'),
            'startdate' => strtotime(Request::get('startdate')),
            'enddate' => strtotime(Request::get('enddate')),
            'send_mail' => Request::int('send_mail'),
            'task_link' => Request::get('task_link'),
            'required' => Request::int('required'),
            'is_active' => Request::int('is_active'),
        );

        $task->setData($data);
        $task->store();

        $config_files = array();
        foreach ($task->jobs as $job) {
            if (!is_null($job->dokument_id)) {
                $config_files[$job->id] = $job->dokument_id;
            }
            $job->delete();
        }

        $j = 1;
        $files = $this->save_files("Job");

        foreach (explode(" ", Request::get('max_jobs')) as $i) {
            if (is_numeric($i)) {
                $id = Request::get('job_id' . $i);
                $trigger = Request::get('job_trigger' . $i);
                $use_file = Request::get('job_use_config_file' . $i);

                $file = $files[$j]->dokument_id;

                $job_data = array(
                    'name' => Request::get('job_name' . $i),
                    'trigger' => $trigger,
                    'description' => Request::get('job_description' . $i),
                    'task_id' => $task->id
                );

                if (!is_null($use_file)) {
                    if (!is_null($file)) {
                        $job_data['dokument_id'] = $file;
                    } elseif ($id != "new" && !is_null($config_files[$id])) {
                        $job_data['dokument_id'] = $config_files[$id];
                        unset($config_files[$id]);
                    }
                }

                $job = Job::create($job_data);

                if ($trigger == "end" || $trigger == "end_all") {
                    $trigger_data = array(
                        'job_id' => $job->id,
                        'time' => $task->enddate
                    );

                    $trigger = TimeTrigger::create($trigger_data);
                }

                $j++;
            }
        }

        foreach ($config_files as $file) {
            if (!is_null($file)) {
                $document = new Leeroy_StudipDocument($file);
                delete_document($document->getId());
                $document->delete();
            }
        }

        $this->redirect('dozent/edit_task/' . $task->id);
    }

    function delete_task_action($id)
    {
        $task = new Leeroy\Tasks($id);

        if ($task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        $task->delete();
        $this->redirect('index/index');
    }

    function edit_task_action($id)
    {
        $this->task = new Leeroy\Tasks($id);

        if ($this->task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        $this->jenkins = Jenkins::find($this->seminar_id);
        $this->connected = $this->jenkins->isConnected();
    }

    function view_dozent_action($handin_id, $edit_field = null)
    {
        $this->handin = new Leeroy\Handin($handin_id);
        $this->task = new Leeroy\Tasks($this->handin->task_id);

        if ($this->task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        // if the second parameter is present, the passed field shall be edited
        if ($edit_field) {
            $this->edit[$edit_field] = true;
        }

    }

    function update_dozent_action($handin_id)
    {
        $handin = new Leeroy\Handin($handin_id);
        $task = new Leeroy\Tasks($handin->task_id);

        if ($task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        if (Request::get('feedback') !== null && $task->enddate <= time()) {
            $handin->feedback = Request::get('feedback');
            $handin->store();
        }
        if (Request::get('hint') !== null && $task->enddate >= time()) {
            $handin->hint = Request::get('hint');
            $handin->store();
        }

        $this->redirect('dozent/view_dozent/' . $handin_id);
    }


    function view_task_action($id)
    {
        $this->task = new Leeroy\Tasks($id);

        if ($this->task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        $this->participants = Leeroy_CourseMember::findByCourse($this->seminar_id);

        $this->group = array();
        $this->group_names = array();

        foreach ($this->participants as $user) {
            $gruppen = $user->getGroups();

            if ($user->status == 'autor' && !empty($gruppen)) {

                $handins = $this->task->handins->findOneBy('user_id', $user->user_id);
                if (!$handins) {  // create missing entries on the fly
                    $handins = Leeroy\Handin::create(array(
                        'user_id' => $user->user_id,
                        'chdate' => 1,
                        'mkdate' => 1,
                        'task_id' => $this->task->getId()
                    ));
                }

                foreach ($gruppen as $gruppen_id => $gruppen_name) {
                    if (is_null($this->group[$gruppen_id])) {
                        $this->group[$gruppen_id] = array();
                    }

                    $this->group_names[$gruppen_id] = $gruppen_name;

                    array_push($this->group[$gruppen_id], $user);
                }

            }
        }
    }

    function grading_action($group_id, $task_id)
    {
        $this->group_id = $group_id;

        $this->task = new Leeroy\Tasks($task_id);
        if ($this->task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        $this->group_name = GetStatusgruppeName($group_id);

        if (is_null($this->group_name)) {
            throw new AccessDeniedException(_('Die Gruppe wurde nicht gefunden!'));
        }

        #var_dump($this->group_name);


        $this->handins = array();

        foreach ($this->task->handins as $handin) {

            if ($handin->isInGroup($group_id)) {
                array_push($this->handins, $handin);
            }

        }

        if (!empty($this->handins)) {
            usort($this->handins, array("Leeroy\Handin", "cmp"));
        }

        #var_dump($this->handings);
    }

    function grading_save_action($task_id, $group_id)
    {
        $this->task = new Leeroy\Tasks($task_id);
        if ($this->task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        $this->group_name = GetStatusgruppeName($group_id);

        if (is_null($this->group_name)) {
            throw new AccessDeniedException(_('Die Gruppe wurde nicht gefunden!'));
        }

        foreach ($_POST as $id => $value) {
            $handin = new Leeroy\Handin($id);
            if (is_numeric($value) && $handin->task->seminar_id == $this->seminar_id) {
                $handin->points = $value;
                $handin->store();
            }
        }

        $this->redirect('dozent/grading/' . $group_id . "/" . $task_id);
    }

    function show_analytics_action($task_id)
    {
        $task = new Leeroy\Tasks($task_id);
        if ($task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }


        $files = array();

        if ($task->hasAnalyticResult()) {
            $data = json_decode($task->analytic);

            foreach ($data->warnings as $warning) {
                if (is_null($files[$warning->fileName])) {
                    $files[$warning->fileName] = array();
                }
                array_push($files[$warning->fileName], $warning);
            }

            foreach ($files as &$file) {
                usort($file, array("IndexController", "analyticCmp"));
            }

            ksort($files);
        }

        $this->files = $files;
        $this->task = $task;
        $this->data = $task;


    }

    function show_test_action($task_id)
    {
        $task = new Leeroy\Tasks($task_id);
        if ($task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        if ($task->hasAnalyticResult()) {
            $data = json_decode($task->test);
        }

        $this->suites = $data->suites;
        $this->task = $task;
        $this->data = $task;


    }

    function show_log_action($task_id)
    {
        $task = new Leeroy\Tasks($task_id);
        if ($task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        Leeroy\Perm::check('new_task', $this->seminar_id);

        $this->data = $task;
        $this->task = $task;
    }

    function download_action($flag = "gt", $group_id = null, $task_id = null)
    {
        $zip_file = HandinFiles::collecting($this->seminar_id, $flag, $group_id, $task_id);

        if (file_exists($zip_file)) {
            header("Content-Type: application/zip");
            header("Content-Disposition: attachment; filename=abgaben.zip");
            header("Content-Length: " . filesize($zip_file));
            ob_clean();
            flush();
            readfile($zip_file);
            exit;
        } else {
            #print_r($zip_file);
            #die();

            throw new Exception("Zip Datei konnte nicht erstellt werden");
        }
    }

    function csv_action($delimer = null)
    {
        if (is_string($delimer) && $delimer == "de") {
            $delimer = ";";
        } else {
            $delimer = ",";
        }

        $participants = Leeroy_CourseMember::findByCourse($this->seminar_id);

        $users = array();

        foreach ($participants as $participant) {
            if ($participant->status == 'autor') {
                array_push($users, $participant);
            }
        }

        usort($users, array("Leeroy_CourseMember", "cmp"));

        $tasks = Leeroy\Tasks::findBySQL("seminar_id = ? AND required = ?", array($this->seminar_id, true));
        usort($tasks, array("Leeroy\Tasks", "cmp"));

        $aux = Leeroy\DataFields::getDataFields($this->seminar_id);

        $header = array("Nachname", "Vorname");
        $content = array();

        foreach ($aux->getHeaders() as $name) {
            array_push($header, $name);
        }

        foreach ($tasks as $task) {
            array_push($header, $task->title);
        }

        array_push($header, "Punkte");
        array_push($content, $header);

        foreach ($users as $user) {
            $line = array(get_nachname($user->user_id), get_vorname($user->user_id));
            $user_aux = $aux->getUserAux($user->user_id);

            foreach ($aux->getHeaders() as $id => $_) {
                array_push($line, $user_aux[$id]);
            }

            $gesamt_punkte = 0;

            foreach ($tasks as $task) {
                $handin = $task->handins->findOneBy('user_id', $user->user_id);

                if (is_object($handin)) {
                    $punkte = $handin->points;
                } else {
                    $punkte = 0;
                }

                if (!is_numeric($punkte)) {
                    $punkte = 0;
                }

                $gesamt_punkte += $punkte;

                array_push($line, $punkte);
            }

            array_push($line, $gesamt_punkte);

            array_push($content, $line);
        }

        $csv_file = tempnam(sys_get_temp_dir(), 'leeroy');

        if (!file_exists($csv_file)) {
            throw new Exception("Konnte Tempfile nicht erstellen");
        }

        $fp = fopen($csv_file, 'w');

        foreach ($content as $line) {
            fputcsv($fp, $line, $delimer);
        }

        fclose($fp);

        header('Content-type: text/csv');
        header('Content-disposition: attachment;filename=auswertung.csv');
        header("Content-Length: " . filesize($csv_file));
        ob_clean();
        flush();
        readfile($csv_file);
        exit;
    }
}