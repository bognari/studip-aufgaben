<?php
/**
 * callback
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
 * Rückrufe von Jenkins werden in callback.php verarbeitet
 */

chdir('../../../');

require '../lib/bootstrap.php';
require_once 'lib/functions.php';
require_once 'vendor/trails/trails.php';

chdir('plugins_packages/TU BS IPS/Leeroy/');


    // for version starting from 2.5 use the same stub
    require_once 'compat/2.5/Leeroy_SimpleCollection.php';
    require_once 'compat/2.5/Leeroy_SimpleORMapCollection.php';
    require_once 'compat/2.5/Leeroy_SimpleORMap.php';
    require_once 'compat/2.5/Leeroy_StudipDocument.php';
    require_once 'compat/2.5/Leeroy_CourseMember.php';


require_once 'app/models/Jenkins.php';
require_once 'app/models/Tasks.php';
require_once 'app/models/Handin.php';
require_once 'app/models/HandinFiles.php';
require_once 'app/models/TaskFiles.php';
require_once 'app/models/Perm.php';
require_once 'app/models/Job.php';
require_once 'app/models/JobBuild.php';
require_once 'app/models/DataFields.php';

/**
 * Die Callback Informationen entpacken
 */
$json = $_POST['json'];
$json = str_replace("\\\"", "\"", $json);
$data = json_decode($json);


if (!\Leeroy\JobBuild::exists($data->token)) {
    throw new AccessDeniedException(sprintf(
            _('Sie haben keine Berechtigung für diese Aktion!'))
    );
}

$jobBuild = new \Leeroy\JobBuild($data->token);

if ($jobBuild->job->id !== $data->id) {
    throw new AccessDeniedException(sprintf(
            _('Sie haben keine Berechtigung für diese Aktion!'))
    );
}

/**
 * Log Datei entgegen nehmen
 */
if (is_string($data->log)) {
    $log = file_get_contents($_FILES[$data->log]['tmp_name']);
    $log = preg_replace("/\/.*" . $jobBuild->job->name . ".*\//U", "", $log);
} else {
    $log = null;
}


/**
 * Herrausfinden was für ein Call es war und Ziel der Ergebnisse holen
 */
switch ($jobBuild->job->trigger) {
    case 'upload':
    case 'end':
        $save = $jobBuild->handin_file->handin;
        break;
    case 'end_all':
        $save = $jobBuild->job->task;
        break;
    default:
        $jobBuild->delete();
        throw new AccessDeniedException(sprintf(_('Kein Unterstützter Trigger!')));
}

/**
 * Testet ob die Analyse erfolgreich war und wenn ja werden die Ergebnisse gespeichert, wenn nicht dann wird versucht das Log zu speichern
 */
if ($jobBuild->job->isSuccessfull($data->buildnumber)) {
    if (in_array('analytics', $data->tasks, true)) {
        $result = $jobBuild->job->getAnalyticResult($data->buildnumber);
        $save->analytic = $result;
    }
    if (in_array('test', $data->tasks, true)) {
        $result = $jobBuild->job->getTestResult($data->buildnumber);
        $save->test = $result;
    }
    if (in_array('moss', $data->tasks, true)) {
        $content = file_get_contents($_FILES[$data->moss]['tmp_name']);
        $match = array();
        $regex = "/http\:\/\/moss\.stanford\.edu\/results\/\d+/";
        $m = preg_match_all($regex, $content, $match);

        if (count($match) > 0 && count($match[0]) > 0 && is_string($match[0][0])) {
            $save->link = $match[0][0];
        }
    }

    $save->lastJob = null;
    $save->store();
} else {
    $save->lastJob = 'fail';
    $save->log = $log;
    $save->store();
}

$jobBuild->delete();


page_close();
