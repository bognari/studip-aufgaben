<?php
/**
 * TaskFiles - Short description for file
 *
 * Long description for file (if any)...
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

/**
 * Class TaskFiles
 * @package Leeroy
 *
 */
class TaskFiles extends \Leeroy_SimpleORMap
{
    /**
     * creates new task_file, sets up relations
     *
     * @param string $id
     */
    public function __construct($id = null)
    {
        $this->db_table = 'leeroy_task_files';

        $this->has_one['document'] = array(
            'class_name' => 'Leeroy_StudipDocument',
            'foreign_key' => 'dokument_id',
            'assoc_foreign_key' => 'dokument_id'
        );

        $this->belongs_to['task'] = array(
            'class_name' => 'Leeroy\Tasks',
            'foreign_key' => 'task_id',
            'assoc_foreign_key' => 'id'
        );

        parent::__construct($id);
    }
}