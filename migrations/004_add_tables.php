<?php

/**
 * AddTables - Migration to initialize DB-structure
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
class AddTables extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `leeroy_tasks`
              ADD COLUMN `upper_bound_points` INT NULL DEFAULT 100 AFTER `log`,
              ADD COLUMN `lower_bound_points` INT NULL DEFAULT -100 AFTER `upper_bound_points`;
        ");
    }

    function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `leeroy_tasks`
              DROP COLUMN `lower_bound_points`,
              DROP COLUMN `upper_bound_points`;
        ");
    }
}
