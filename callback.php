<?php
/**
 * Created by IntelliJ IDEA.
 * User: stephan
 * Date: 13.02.15
 * Time: 02:03
 */

chdir('../../../');

require '../lib/bootstrap.php';
require_once 'lib/functions.php';
require_once 'vendor/trails/trails.php';

chdir('plugins_packages/TU BS IPS/Leeroy/');

if (version_compare($GLOBALS['SOFTWARE_VERSION'], '2.4', '<=')) {
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/StudipArrayObject.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/Leeroy_SimpleCollection.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/Leeroy_SimpleORMapCollection.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/Leeroy_SimpleORMap.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/Leeroy_StudipDocument.php';
    require_once 'compat/' . $GLOBALS['SOFTWARE_VERSION'] . '/CourseMember.php';
} else {
    // for version starting from 2.5 use the same stub
    require_once 'compat/2.5/Leeroy_SimpleCollection.php';
    require_once 'compat/2.5/Leeroy_SimpleORMapCollection.php';
    require_once 'compat/2.5/Leeroy_SimpleORMap.php';
    require_once 'compat/2.5/Leeroy_StudipDocument.php';
}

require_once 'app/models/Jenkins.php';
require_once 'app/models/Tasks.php';
require_once 'app/models/Handin.php';
require_once 'app/models/HandinFiles.php';
require_once 'app/models/TaskFiles.php';
require_once 'app/models/Perm.php';
require_once 'app/models/Job.php';
require_once 'app/models/JobBuild.php';
require_once 'app/models/DataFields.php';

#echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
#echo "post\n";
#print_r($_POST);
#echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
#echo "json\n";
$json = $_POST['json'];
#echo $json;
$json = str_replace("\\\"", "\"", $json);
#echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
#echo "json2\n";

#echo $json;


#echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
#echo "data\n";

$data = json_decode($json);
#var_export($data);

#echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
#echo "file\n";

if (is_string($data->log)) {
    #echo "\n\nhat log:\n";
    $log = file_get_contents($_FILES[$data->log]['tmp_name']);
} else {
    #echo "\n\nhat KEIN log:\n";
    $log = null;
}

#die();

if (!\Leeroy\JobBuild::exists($data->token)) {
    throw new AccessDeniedException(sprintf(
            _('Sie haben keine Berechtigung für diese Aktion!'))
    );
}

$jobBuild = new \Leeroy\JobBuild($data->token);

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

if ($jobBuild->job->isSuccessfull($data->buildnumber)) {
    if (in_array('analytics', $data->tasks, true)) {
        $result = $jobBuild->job->getAnalyticResult($data->buildnumber);
        #echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
        #echo "analytics\n";
        #print_r($result);
        $save->analytic = $result;
    }
    if (in_array('test', $data->tasks, true)) {
        $result = $jobBuild->job->getTestResult($data->buildnumber);
        #echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
        #echo "test\n";
        #print_r($result);
        $save->test = $result;
    }
    if (in_array('extern', $data->tasks, true)) {
        #echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
        #echo "content:\n";
        $content = file_get_contents($_FILES[$data->extern]['tmp_name']);
        #print_r($content);
        $match = array();
        #preg_match_all("/(.*)(http\:\/\/moss\.stanford\.edu\/results\/\d+)/", $input_lines, $output_array);
        $regex = "/http\:\/\/moss\.stanford\.edu\/results\/\d+/";
        $m = preg_match_all($regex, $content, $match);
        #echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
        #echo "match\n";
        #var_dump($match);
        #echo "\n\n\nm:\n";
        #print_r($m);

        #echo "\n\n\nregex:\n";
        #print_r($regex);

        if (count($match) > 0 && count($match[0]) > 0 && is_string($match[0][0])) {

            $save->link = $match[0][0];
        }
    }

    $save->lastJob = null;
    $save->store();
} else {
    #echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
    #echo "log\n";
    #print_r($log);
    $save->lastJob = 'fail';
    $save->log = $log;
    $save->store();
}

$jobBuild->delete();


page_close();



