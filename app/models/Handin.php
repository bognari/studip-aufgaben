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

    /**
     * Gibt die Gruppen des Studenten als Array zurück
     * @return string[]
     */
    public function getGroups()
    {
        return GetGroupsByCourseAndUser($this->task->seminar_id, $this->user_id);
    }

    /**
     * Testet ob der Student sich in der Gruppe befindet
     * @param string $group die ID der jeweiligen Gruppe
     * @return bool
     */
    public function isInGroup($group)
    {
        return array_key_exists($group, GetGroupsByCourseAndUser($this->task->seminar_id, $this->user_id));
    }

    /**
     * Sortierbedinnung zum Sortieren von Abgaben nach dem Namen der Studenten
     * @param Handin $a
     * @param Handin $b
     * @return int
     */
    public static function cmp($a, $b)
    {
        return (get_fullname($a->user_id) < get_fullname($b->user_id)) ? -1 : 1;
    }

    /**
     * @return HandinFiles
     */
    public function getFileAnswer()
    {
        $file_answer = $this->files->findOneBy('type', 'answer');
        return $file_answer;
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
    public function hasLog()
    {
        return is_string($this->log);
    }

    /**
     * @return bool
     */
    public function hasPoints()
    {
        return is_numeric($this->points);
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
     * @return array
     */
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

    /**
     * Überschreibt die store Methode aus dem ORM, um Abgaben von Studenten, die in der gleichen Abgabegruppe, außer die Bewertung sind zu synchronisieren
     * @param bool $sync
     */
    public function store($sync = true)
    {

        if ($sync === true) {
            $handins = $this->getSyncHandins();

            foreach ($handins as $handin) {
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
                    $handin_file = HandinFiles::create($data);
                }

                $handin->store(false);
            }
        }

        $ret = parent::store();

        return $ret;
    }

    /**
     * Gibt alle Abgaben als Array zurück, mit denen die Abgabe gesynct ist.
     * @return Handin[]
     */
    private function getSyncHandins()
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
                        $h = $this->task->handins->findOneBy('user_id', $user);
                        if ($h !== null) {
                            array_push($handins, $h);
                        }
                    }
                }
            }
        }

        return $handins;
    }

    /**
     * Fügt der Abgabe eine Datei hinzu und löst den upload Trigger aus
     * @param string $file id der Datei
     * @param string $type Typ der Datei (answer oder feedback)
     * @param string $url die URL der callback.php
     * @return mixed
     */
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

    /**
     * @param string $user_id
     * @return bool
     */
    function belongsTo($user_id)
    {
        $handins = $this->getSyncHandins();

        foreach ($handins as $h) {
            if ($h->user_id == $user_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $seminar_id
     * @param string $user_id
     * @return int
     */
    public static function getTotalPoints($seminar_id, $user_id)
    {

        $tasks = Tasks::findBySQL('seminar_id = ? AND required = ?', array($seminar_id, true));

        $gesamt_punkte = 0;

        foreach ($tasks as $task) {
            $handin = $task->handins->findOneBy('user_id', $user_id);

            $punkte = 0;

            if (is_object($handin)) {
                $punkte = $handin->points;
            }

            if (!is_numeric($punkte)) {
                $punkte = 0;
            }

            $gesamt_punkte += $punkte;
        }

        return $gesamt_punkte;
    }
}