<?php
/**
 * Handin - represents an entry in task_users
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

namespace Leeroy;

class Handin extends \Leeroy_SimpleORMap
{
    /**
     * creates new task_user, sets up relations
     *
     * @param string $id
     */
    public function __construct($id = null)
    {
        $this->db_table = 'leeroy_handin';

        $this->has_many['files'] = array(
            'class_name' => 'Leeroy\HandinFiles',
            'assoc_foreign_key' => 'handin_id',
            'foreign_key' => 'id'
        );


        $this->belongs_to['task'] = array(
            'class_name' => 'Leeroy\Tasks',
            'foreign_key' => 'task_id',
            'assoc_foreign_key' => 'id'
        );

        parent::__construct($id);
    }

    public function getGroups()
    {
        return GetGroupsByCourseAndUser($this->task->seminar_id, $this->user_id);
    }

    public function isInGroup($gruop)
    {
        return array_key_exists($gruop, GetGroupsByCourseAndUser($this->task->seminar_id, $this->user_id));
    }

    public static function cmp($a, $b)
    {
        return (get_fullname($a->user_id) < get_fullname($b->user_id)) ? -1 : 1;
    }

    public function getFileAnswer()
    {
        $file_answer = $this->files->findOneBy('type', 'answer');
        return $file_answer;
    }

    public function hasLinkResult()
    {
        return is_string($this->link) && $this->link !== 'fail';
    }

    public function hasAnalyticResult()
    {
        return is_string($this->analytic) && $this->analytic !== 'fail';
    }

    public function hasTestResult()
    {
        return is_string($this->test) && $this->test !== 'fail';
    }

    public function hasLog()
    {
        return is_string($this->log);
    }

    public function hasPoints()
    {
        return is_numeric($this->points);
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

    public function getAnalyticResult()
    {
        $files = array();

        if ($this->hasAnalyticResult()) {
            $data = json_decode($this->analytic);
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

    public function getTestResult()
    {
        if ($this->hasTestResult()) {
            $data = json_decode($this->test);

            return $data->suites;
        }

        return array();
    }
}
