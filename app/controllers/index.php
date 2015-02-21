<?php
/**
 * IndexController - main controller for the plugin
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      Ramus Fuhse <fuhse@data-quest.de>
 * @author      Stephan Mielke
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
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

/**
 * @property Leeroy\Tasks task
 * @property  seminar_id
 * @property string sort
 */
class IndexController extends LeeroyStudipController
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

    function index_action()
    {
        if (!Request::option('sort_by')
            || in_array(Request::option('sort_by'), words('title startdate enddate')) === false
        ) {
            $this->sort = 'enddate';
            $this->order = 'desc';
        } else {
            $this->sort = Request::option('sort_by');
            $this->order = Request::option('asc') ? 'asc' : 'desc';
        }

        if (Leeroy\Perm::has('new_task', $this->seminar_id)) { // tutor ansicht
            $this->tasks = Leeroy\Tasks::findBySQL("seminar_id = ?
                ORDER BY {$this->sort} {$this->order}, startdate DESC", array($this->seminar_id));
        } else { // studenten ansicht

            $jenkins = new Leeroy\Jenkins($this->seminar_id);


            if ($jenkins->force_data) { // testen ob zusatzangaben benötigt werden
                $this->aux_regex = json_decode($jenkins->aux);
                $this->user_id = $GLOBALS['user']->id;
                $this->aux_data = Leeroy\DataFields::getDataFields($this->seminar_id);
                $this->aux_headers = $this->aux_data->getHeaders();
                $this->aux_user = $this->aux_data->getUserAux($this->user_id);
                $this->aux = $this->aux_data->isValid($this->user_id, $this->aux_regex);
            }


            $this->tasks = Leeroy\Tasks::findBySQL("seminar_id = ? /* AND startdate <= UNIX_TIMESTAMP() */
                ORDER BY {$this->sort} {$this->order}, startdate DESC", array($this->seminar_id));

            // reorder all running tasks if necessary - the task with the shortest time frame shall be first
            if (!empty($this->tasks) && $this->sort == 'enddate') {
                foreach ($this->tasks as $task) {
                    $reorder[$task->getStatus()][] = $task;
                }
                if (!empty($reorder['running'])) {
                    $reorder['running'] = array_reverse($reorder['running']);

                    $new_order = array();

                    foreach (words('future running past') as $status) {
                        if (!empty($reorder[$status])) {
                            $new_order = array_merge($new_order, $reorder[$status]);
                        }
                    }

                    $this->tasks = $new_order;
                }
            }
        }
        $this->jenkins = Leeroy\Jenkins::find($this->seminar_id);
    }

    function show_analytics_action($handin_id, $wait = false)
    {
        $handin = new Leeroy\Handin($handin_id);
        if ($handin->task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        if (!(Leeroy\Perm::has('new_task', $this->seminar_id) || $handin->user_id == $GLOBALS['user']->id)) {
            throw new AccessDeniedException(_('Kein Zugriff!'));
        }

        $files = array();

        if ($wait != false && is_object($handin->getFileAnswer())) {
            $jobbuild = Leeroy\JobBuild::findBySQL("handin_file_id = ?", array($handin->getFileAnswer()->id));
            if (is_null($handin->analytic) && is_null($handin->lastJob) && !empty($jobbuild)) {
                $this->redirect('index/analytics_reload/' . $handin_id);
            }
        }

        if ($handin->hasAnalyticResult()) {
            $data = json_decode($handin->analytic);

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

        $this->data = $handin;
        $this->files = $files;
        $this->task = $handin->task;
    }

    function show_test_action($handin_id, $wait = false)
    {
        $handin = new Leeroy\Handin($handin_id);
        if ($handin->task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        if (!(Leeroy\Perm::has('new_task', $this->seminar_id) || $handin->user_id == $GLOBALS['user']->id)) {
            throw new AccessDeniedException(_('Kein Zugriff!'));
        }

        /*if ($wait != false && is_object($handin->getFileAnswer())) {
            $jobbuild = Leeroy\JobBuild::findBySQL("handin_file_id = ?", array($handin->getFileAnswer()->id));
            if (is_null($handin->test) && is_null($handin->lastJob) && !empty($jobbuild) ) {
                $this->redirect('index/analytics_reload/' . $handin_id);
            }
        }*/

        if ($handin->hasAnalyticResult()) {
            $data = json_decode($handin->test);
        }

        $this->data = $handin;
        $this->suites = $data->suites;
        $this->task = $handin->task;
    }

    public static function analyticCmp($a, $b)
    {
        return ($a->primaryLineNumber < $b->primaryLineNumber) ? -1 : 1;
    }

    function analytics_reload_action($handin_id)
    {
        $this->handin_id = $handin_id;
    }

    function show_log_action($handin_id)
    {
        $handin = new Leeroy\Handin($handin_id);
        if ($handin->task->seminar_id != $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        if (!(Leeroy\Perm::has('new_task', $this->seminar_id) || $handin->user_id == $GLOBALS['user']->id)) {
            throw new AccessDeniedException(_('Kein Zugriff!'));
        }

        $this->data = $handin;
        $this->task = $handin->task;
    }
}
