<?php
/**
 * HandinFiles - Short description for file
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

class HandinFiles extends \Leeroy_SimpleORMap
{
    /**
     * creates new task_user_file, sets up relations
     *
     * @param string $id
     */
    public function __construct($id = null)
    {
        $this->db_table = 'leeroy_handin_files';

        $this->has_one['document'] = array(
            'class_name' => 'Leeroy_StudipDocument',
            'foreign_key' => 'dokument_id',
            'assoc_foreign_key' => 'dokument_id'
        );

        $this->belongs_to['handin'] = array(
            'class_name' => 'Leeroy\Handin',
            'foreign_key' => 'handin_id',
            'assoc_foreign_key' => 'id'
        );

        parent::__construct($id);
    }

    public static function collecting($seminar_id, $flag = "", $group_id = false, $task_id = null)
    {

        $tempfile = tempnam(sys_get_temp_dir(), 'leeroy');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }

        @mkdir($tempfile);

        if (!is_dir($tempfile)) {
            throw new \Exception("Konnte Tempordner nicht erstellen");
        }

        $groups = \Leeroy_CourseMember::getGroupsForCourse($seminar_id);


        if ($group_id != false && is_null($groups['names'][$group_id])) {
            throw new \AccessDeniedException(_('Gruppe nicht gefunden!'));
        }

        if ($group_id != false) {
            $groups['names'] = array($group_id => $groups['names'][$group_id]);
            $groups['members'] = array($group_id => $groups['members'][$group_id]);
        }

        #print_r($groups);

        if (is_null($task_id)) {
            $tasks = Tasks::findBySQL("seminar_id = ?", array($seminar_id));
        } else {
            $tasks = Tasks::findBySQL("seminar_id = ? AND id = ?", array($seminar_id, $task_id));
        }

        if (empty($tasks)) {
            throw new \AccessDeniedException(_('Aufgabe nicht gefunden!'));
        }

        # gruppen ordner erstellen
        foreach ($groups["names"] as $group_id => $group_name) {
            if (strpos($flag, "g") === false) {
                $group_dir = $tempfile;
            } else {
                $group_dir = $tempfile . "/" . $group_name;
                @mkdir($group_dir);
            }

            # benutzer durch gehen
            #print_r($groups["members"][$group_id]);

            foreach ($groups["members"][$group_id] as $user) {

                # aufgaben durch gehen
                foreach ($tasks as $task) {
                    if (strpos($flag, "t") === false) {
                        $task_dir = $group_dir;
                    } else {
                        $task_dir = $group_dir . "/" . $task->title;
                        @mkdir($task_dir);
                    }

                    $handin = $task->handins->findOneBy("user_id", $user->user_id);

                    if (is_object($handin) && is_object($handin->getFileAnswer())) {
                        $path = $task_dir . "/" . preg_replace('/[^A-Za-z0-9_\-]/', '_', get_fullname($user->user_id));
                        @mkdir($path);

                        $file = $handin->getFileAnswer()->document->id;
                        $zip_file = get_upload_file_path($file);

                        $zip = new \ZipArchive;
                        if ($zip->open($zip_file) === TRUE) {
                            $zip->extractTo($path . "/");
                            $zip->close();
                            #echo 'ok';
                        } else {
                            #echo 'failed';
                        }

                        if ($handin->hasAnalyticResult()) {
                            file_put_contents($path . "/analytic.txt", $handin->analytic);
                        }

                        if ($handin->hasTestResult()) {
                            file_put_contents($path . "/test.txt", $handin->test);
                        }

                        if ($handin->hasLog()) {
                            file_put_contents($path . "/log.txt", $handin->log);
                        }
                    }
                }
            }
        }


        $file_name = $tempfile . "/abgaben.zip";

        if (HandinFiles::zipFile($tempfile, $file_name, $flag = '') === false) {
            #print_r($file_name);
            #die();
            throw new \Exception("Zip Datei konnte nicht erstellt werden");
        }

        return $file_name;
    }

    function zipFile($source, $destination, $flag = '')
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));
        if ($flag) {
            $flag = basename($source) . '/';
        }

        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {

                $file = str_replace('\\', '/', realpath($file));

                if (strpos($flag . $file, $source) !== false) { // this will add only the folder we want to add in zip

                    if (is_dir($file) === true) {
                        $zip->addEmptyDir(str_replace($source . '/', '', $flag . $file . '/'));

                    } else if (is_file($file) === true) {
                        $zip->addFromString(str_replace($source . '/', '', $flag . $file), file_get_contents($file));
                    }
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString($flag . basename($source), file_get_contents($source));
        }

        return $zip->close();
    }
}