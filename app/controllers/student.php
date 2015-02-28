<?php
/**
 * StudentController
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

class StudentController extends LeeroyStudipController
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

    function view_student_action($id, $edit_field = null)
    {
        // if the second parameter is present, the passed field shall be edited
        if ($edit_field) {
            $this->edit[$edit_field] = true;
        }

        $this->task = new Leeroy\Tasks($id);

        if ($this->task->startdate > time() || $this->task->seminar_id !== $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        $this->handin = $this->task->handins->findOneBy('user_id', $GLOBALS['user']->id);

        if (!$this->handin) {
            $data = array(
                'task_id' => $id,
                'user_id' => $GLOBALS['user']->id
            );

            $this->handin = Leeroy\Handin::create($data);
        }

    }

    function update_student_action($task_id, $handin_id)
    {
        $task = new Leeroy\Tasks($task_id);

        if ($task->startdate > time() || $task->enddate < time()) {
            throw new AccessDeniedException(_('Sie dürfen diese Aufgabe nicht bearbeiten!'));
        }

        if ($task->seminar_id !== $this->seminar_id) {
            throw new AccessDeniedException(_('Die Aufgabe wurde nicht gefunden!'));
        }

        $data = array(
            'task_id' => $task_id,
            'user_id' => $GLOBALS['user']->id,
            'answer' => Request::get('answer')
        );

        $handin = new Leeroy\Handin($handin_id);
        $handin->setData($data);
        $handin->store();

        $this->redirect('student/view_student/' . $task_id);
    }
}