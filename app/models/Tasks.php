<?php
/**
 * Tasks - presents a single task
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
 *
 */

namespace Leeroy;

class Tasks extends \Leeroy_SimpleORMap
{
    /**
     * creates new task, sets up relations
     *
     * @param string $id
     */
    public function __construct($id = null)
    {
        $this->db_table = 'leeroy_tasks';

        $this->belongs_to['jenkins'] = array(
            'class_name' => 'Leeroy\Jenkins',
            'foreign_key' => 'seminar_id',
            'assoc_foreign_key' => 'seminar_id'
        );

        $this->has_many['handins'] = array(
            'class_name' => 'Leeroy\Handin',
            'foreign_key' => 'id',
            'assoc_foreign_key' => 'task_id'
        );

        $this->has_many['files'] = array(
            'class_name' => 'Leeroy\TaskFiles',
            'foreign_key' => 'id',
            'assoc_foreign_key' => 'task_id'
        );

        $this->has_many['jobs'] = array(
            'class_name' => 'Leeroy\Job',
            'foreign_key' => 'id',
            'assoc_foreign_key' => 'task_id'
        );

        parent::__construct($id);
    }

    /**
     * returns a status string denoting the run-status of the current task
     *
     * @return string|boolean
     */
    public function getStatus()
    {
        if (!$this->is_active) {
            return 'inactiv';
        }
        if ($this->startdate <= time() && $this->enddate >= time()) {
            return 'running';
        } else if ($this->enddate < time()) {
            return 'past';
        } else if ($this->startdate > time()) {
            return 'future';
        }

        return false;
    }

    /**
     * returns a human readable version of the run-status
     *
     * @return string
     */
    public function getStatusText()
    {
        switch ($this->getStatus()) {
            case 'inactiv':
                return _('deaktiviert');
                break;

            case 'running':
                return _('läuft');
                break;

            case 'past':
                return _('beendet');
                break;

            case 'future':
                return _('läuft noch nicht');
                break;
        }
    }

    public function hasTaskLink()
    {
        return strlen($this->task_link) > 0;
    }

    public function hasLinkResult()
    {
        return is_string($this->link) && $this->link != "fail";
    }

    public function hasAnalyticResult()
    {
        return is_string($this->analytic) && $this->analytic != "fail";
    }

    public function hasTestResult()
    {
        return is_string($this->test) && $this->test != "fail";
    }

    public function hasMaterial()
    {
        return count($this->files) > 0;
    }

    public function hasJobs()
    {
        return count($this->jobs) > 0;
    }

    public function isRequired()
    {
        return $this->required == 1;
    }

    public function hashUploadTrigger()
    {
        foreach ($this->jobs as $job) {
            if ($job->trigger == "upload") {
                return true;
            }
        }
        return false;
    }

    public function getAnalyticWarnings()
    {
        if ($this->hasAnalyticResult()) {
            $data = json_decode($this->analytic);

            return $data->numberOfWarnings;
        }

        return 0;
    }

    public function getTestErrors()
    {
        if ($this->hasTestResult()) {
            $data = json_decode($this->test);

            return $data->failCount;
        }

        return 0;
    }

    public function hasLog()
    {
        return is_string($this->log);
    }

    public static function cmp($a, $b)
    {
        return $a->enddate < $b->enddate ? -1 : 1;
    }
}
