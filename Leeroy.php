<?php
/**
 * Leeroy.php - Main plugin class, routes to trailified plugin
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Gl?ggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

// load legacy code for older Stud.IP-Versions
if (version_compare($GLOBALS['SOFTWARE_VERSION'], "2.4", '<=')) {
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/StudipArrayObject.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/Leeroy_SimpleCollection.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/Leeroy_SimpleORMapCollection.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/Leeroy_SimpleORMap.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/Leeroy_StudipDocument.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/CourseMember.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/Leeroy_CourseMember.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/DataFild.php';
} else {
    // for version starting from 2.5 use the same stub
    require_once 'compat/2.5/Leeroy_SimpleCollection.php';
    require_once 'compat/2.5/Leeroy_SimpleORMapCollection.php';
    require_once 'compat/2.5/Leeroy_SimpleORMap.php';
    require_once 'compat/2.5/Leeroy_StudipDocument.php';
    require_once 'compat/2.5/Leeroy_CourseMember.php';
    require_once 'compat/2.5/DataFields.php';
}

require_once 'app/models/Jenkins.php';
require_once 'app/models/Tasks.php';
require_once 'app/models/TaskFiles.php';
require_once 'app/models/Handin.php';
require_once 'app/models/HandinFiles.php';
require_once 'app/models/Perm.php';
require_once 'app/models/Job.php';
require_once 'app/models/JobBuild.php';
require_once 'app/models/TimeTrigger.php';

#if (!function_exists('curl_init')) {
#    require_once 'lib/purl/Purl.php';
#}

class Leeroy extends StudIPPlugin implements StandardPlugin
{
    /**
     * Does nothing if plugin is not activated in the current course.
     * In Stud.IP versions prior 2.5 navigation is built here
     * 
     * @return type
     */
    function __construct()
    {

        parent::__construct();

        Leeroy\TimeTrigger::execute($this->getPluginURL());

        if (!$this->isActivated()) {
            return;
        }

        $GLOBALS['Leeroy_path'] = $this->getPluginURL();

        if (Navigation::hasItem("/course") && version_compare($GLOBALS['SOFTWARE_VERSION'], "2.3", '>=')) {
            $navigation = $this->getTabNavigation(Request::get('cid', $GLOBALS['SessSemName'][1]));
            Navigation::insertItem('/course/leeroy', $navigation['leeroy'], 'members');
        }
    }

    /**
     * Returns the in-course navigation
     * 
     * @param type $course_id
     * @return type
     */
    public function getTabNavigation($course_id)
    {
        $navigation = new Navigation(_('Leeroy'), PluginEngine::getLink('leeroy/index'));
        $navigation->setImage('icons/16/white/assessment.png');

        return array('leeroy' => $navigation);
    }

    /**
     * returns the navigation-icon for the course-overview
     * 
     * @param type $course_id
     * @param type $last_visit
     * @param type $user_id
     * @return \Navigation
     */
    public function getIconNavigation($course_id, $last_visit, $user_id = null)
    {
        if (!$this->isActivated($course_id)) {
            return;
        }

        $navigation = new Navigation('leeroy', PluginEngine::getLink('leeroy/index'));
        $navigation->setImage('icons/16/grey/assessment.png', array(
            'title' => _('Es gibt nichts neues seit Ihrem letzten Besuch.')
        ));

        // for lecturers show the number of new activites from their students
        if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
            $tasks = Leeroy\Tasks::findBySQL('seminar_id = ?', array($course_id));

            $act_num = 0;
            foreach ($tasks as $task) {
                $tu = Leeroy\Handin::findBySQL('task_id = ? AND mkdate >= ?', array($task->id, $last_visit));
                if (!empty($tu)) {
                    $act_num += sizeof($tu);
                }
            }

            if ($act_num > 0) {
                $navigation->setImage('icons/16/red/assessment.png', array(
                    'title' => sprintf(_('Seit Ihrem letzten Besuch gibt es %s neue Aktivit�ten'), $act_num)
                ));
            }
        } else {    // for students show the number of new, visible, tasks
            $tasks = Leeroy\Tasks::findBySQL('seminar_id = ? AND mkdate >= ?
                AND startdate <= UNIX_TIMESTAMP()',
                array($course_id, $last_visit));

            if (sizeof($tasks) > 0) {
                $navigation->setImage('icons/16/red/assessment.png', array(
                    'title' => sprintf(_('Seit Ihrem letzten Besuch gibt es %s neue Aufgaben.'), sizeof($tasks))
                ));
            }
        }

        #$navigation->setBadgeNumber($num_entries);

        return $navigation;
    }

    /**
     * This plugin does currently not return any notification objects
     * 
     * @param type $course_id
     * @param type $since
     * @param type $user_id
     * @return type
     */
    function getNotificationObjects($course_id, $since, $user_id)
    {
        return array();
    }

    const DEFAULT_CONTROLLER = "index";

    /**
     * route the request to the controllers
     * 
     * @param string $unconsumed_path
     */
    function perform($unconsumed_path)
    {
        $trails_root = $this->getPluginPath() . "/app";
        $dispatcher = new Trails_Dispatcher($trails_root,
                                            rtrim(PluginEngine::getURL($this, null, ''), '/'),
                                            self::DEFAULT_CONTROLLER);
        $dispatcher->plugin = $this;
        #print_r($unconsumed_path);
        $dispatcher->dispatch($unconsumed_path);
    }

    public function getInfoTemplate($course_id)
    {
        return null;
    }
}