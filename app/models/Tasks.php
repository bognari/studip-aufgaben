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

/**
 * Class Tasks
 * @package Leeroy
 * Stellt eine Aufgabe dar
 */
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
            return 'inactive';
        }
        if ($this->startdate <= time() && $this->enddate >= time()) {
            return 'running';
        } elseif ($this->enddate < time()) {
            return 'past';
        } elseif ($this->startdate > time()) {
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
            case 'inactive':
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

        return _('unbekannt');
    }

    /**
     * @return bool
     */
    public function hasTaskLink()
    {
        return $this->task_link !== '' && $this->task_link !== null;
    }

    /**
     * @return bool
     */
    public function hasLinkResult()
    {
        return is_string($this->link) && $this->link !== 'fail';
    }

    /**
     * @return bool
     */
    public function hasAnalyticResult()
    {
        return is_string($this->analytic) && $this->analytic !== 'fail';
    }

    /**
     * @return bool
     */
    public function hasTestResult()
    {
        return is_string($this->test) && $this->test !== 'fail';
    }

    /**
     * @return bool
     */
    public function hasMaterial()
    {
        return count($this->files) > 0;
    }

    /**
     * @return bool
     */
    public function hasJobs()
    {
        return count($this->jobs) > 0;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required === '1';
    }

    /**
     * @return bool
     */
    public function hashUploadTrigger()
    {
        foreach ($this->jobs as $job) {
            if ($job->trigger === 'upload') {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getAnalyticWarnings()
    {
        if ($this->hasAnalyticResult()) {
            $data = json_decode($this->analytic);

            return $data->numberOfWarnings;
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getTestErrors()
    {
        if ($this->hasTestResult()) {
            $data = json_decode($this->test);

            return $data->failCount;
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function hasLog()
    {
        return is_string($this->log);
    }

    /**
     * Sortiertv zwei Aufgaben nach Enddatum
     * @param Tasks $a
     * @param Tasks $b
     * @return int
     */
    public static function cmp($a, $b)
    {
        return $a->enddate < $b->enddate ? -1 : 1;
    }

    /**
     * @return array
     */
    public function getAnalyticResult()
    {
        $data = json_decode($this->analytic);
        $files = array();

        if ($this->hasAnalyticResult()) {
            foreach ($data->warnings as $warning) {
                if ($files[$warning->fileName] === null) {
                    $files[$warning->fileName] = array();
                }
                array_push($files[$warning->fileName], $warning);
            }

            foreach ($files as &$file) {
                usort($file, array('Leeroy\Job', 'analyticCmp'));
            }

            ksort($files);
        }

        return $files;
    }

    /**
     * @return array
     */
    public function getTestResult()
    {
        if ($this->hasTestResult()) {
            $data = json_decode($this->test);

            return $data->suites;
        }

        return array();
    }
}
