<?php
/**
 * TimeTrigger
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Stephan Mielke
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 *
 */

namespace Leeroy;

/**
 * Class TimeTrigger
 * @package Leeroy
 * Verwaltet Zeit gesteuerte Job Ausführungen
 */
class TimeTrigger extends \Leeroy_SimpleORMap
{
    /**
     * creates new task, sets up relations
     *
     * @param string $id
     */
    public function __construct($id = null)
    {
        $this->db_table = 'leeroy_job_timetrigger';

        $this->belongs_to['job'] = array(
            'class_name' => 'Leeroy\Job',
            'foreign_key' => 'job_id',
            'assoc_foreign_key' => 'id'
        );

        parent::__construct($id);
    }

    /**
     * Testet ob Jobs gestartet werden sollen und wenn ja, werden diese ausgeführt
     * @param string $callback_url
     * @throws \AccessDeniedException
     * @throws \ErrorException
     */
    public static function execute($callback_url)
    {
        $token = md5(uniqid('padme', true));

        $query = 'UPDATE leeroy_job_timetrigger AS tt SET tt.worker = ? WHERE tt.time < ? AND tt.worker IS NULL';
        $statement = \DBManager::get()->prepare($query);
        $statement->execute(array($token, time()));

        $tts = TimeTrigger::findBySQL('worker = ?', array($token));

        foreach ($tts as $tt) {
            switch ($tt->job->trigger) {
                case 'end' : {
                    foreach ($tt->job->task->handins as $handin) {
                        $handin_file = $handin->getFileAnswer();
                        if (is_object($handin_file)) {
                            $tt->job->execute(get_upload_file_path($handin_file->document->id), $callback_url, $handin_file->id);
                        }
                    }
                    break;
                }
                case 'end_all' : {
                    $zip_file = HandinFiles::collecting($tt->job->task->seminar_id, '', false, $tt->job->task->id);
                    $tt->job->execute($zip_file, $callback_url);
                    unset($zip_file);
                    break;
                }
            }
            $tt->delete();
        }
    }
}
