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

    public function store($sync = true)
    {

        if ($sync === true) {
            #print_r($this);
            #echo "<br><br>";
            $handins = $this->getSyncHandins();
            #print_r($handins);
            #die();


            foreach ($handins as $handin) {

                #print_r($handin);
                #die();

                $handin->hint = $this->hint;
                $handin->answer = $this->answer;
                $handin->feedback = $this->feedback;
                $handin->analytic = $this->analytic;
                $handin->test = $this->test;
                $handin->link = $this->link;
                $handin->lastJob = $this->lastJob;
                $handin->log = $this->log;

                foreach ($handin->files as $file) {
                    $file->delete();
                }

                foreach ($this->files as $file) {
                    $data = array(
                        'dokument_id' => $file->dokument_id,
                        'handin_id' => $handin->id,
                        'type' => $file->type
                    );

                    #print_r($data);
                    #echo "<br><br>";
                    #print_r($this);

                    #die();

                    $handin_file = HandinFiles::create($data);
                }

                $handin->store(false);
            }
        }
        parent::store();
    }

    public function getSyncHandins()
    {
        $handins = array();

        foreach ($this->getGroups() as $id => $name) {
            if (is_string($this->task->jenkins->group_sync_regex) && preg_match("/^" . $this->task->jenkins->group_sync_regex . "$/", $name, $output_array) === 1) {
                $query = "SELECT user_id
                  FROM statusgruppen
                  JOIN statusgruppe_user USING (statusgruppe_id)
                  WHERE statusgruppe_id = ?";
                $statement = \DBManager::get()->prepare($query);
                $statement->execute(array($id));

                $users = $statement->fetchAll(\PDO::FETCH_COLUMN);

                foreach ($users as $user) {
                    if ($user !== $this->user_id) {
                        array_push($handins, $this->task->handins->findOneBy('user_id', $user));
                    }
                }
            }
        }

        return $handins;
    }

    public function addFile($file, $type, $url)
    {
        $data = array(
            'dokument_id' => $file->id,
            'handin_id' => $this->id,
            'type' => $type
        );

        $handin_file = HandinFiles::create($data);

        if ($handin_file->type === 'answer') {

            $this->analytic = null;
            $this->test = null;
            $this->link = null;
            $this->lastJob = null;
            $this->log = null;
            $this->store(false);

            foreach ($this->task->jobs as $job) { # trigger
                if ($job->trigger === 'upload') {
                    $job->execute(get_upload_file_path($file->getId()), $url, $handin_file->id);
                }
            }
        }

        $handin = new Handin($this->id);
        $handin->store();


        return $handin_file;
    }
}