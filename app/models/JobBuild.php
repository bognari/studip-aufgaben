<?php
/**
 * JobBuild
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
 * Class JobBuild
 * @package Leeroy
 * Platzhalter für gestartete Analysen
 */
class JobBuild extends \Leeroy_SimpleORMap
{
    /**
     * creates new task_user_file, sets up relations
     *
     * @param string $id
     */
    public function __construct($id = null)
    {
        $this->db_table = 'leeroy_job_build';

        $this->belongs_to['job'] = array(
            'class_name' => 'Leeroy\Job',
            'foreign_key' => 'job_id',
            'assoc_foreign_key' => 'id'
        );

        $this->belongs_to['handin_file'] = array(
            'class_name' => 'Leeroy\HandinFiles',
            'foreign_key' => 'handin_file_id',
            'assoc_foreign_key' => 'id'
        );

        # ein job muss seine builds nicht kennen

        parent::__construct($id);
    }
}