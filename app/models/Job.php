<?php
/**
 * Job
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

class Job extends \Leeroy_SimpleORMap
{
    /**
     * creates new task_user_file, sets up relations
     *
     * @param string $id
     */
    public function __construct($id = null)
    {
        $this->db_table = 'leeroy_job';

        $this->belongs_to['task'] = array(
            'class_name' => 'Leeroy\Tasks',
            'foreign_key' => 'task_id',
            'assoc_foreign_key' => 'id'
        );

        $this->has_one['file'] = array(
            'class_name' => 'Leeroy_StudipDocument',
            'foreign_key' => 'dokument_id',
            'assoc_foreign_key' => 'dokument_id'
        );

        # ein job muss seine builds nicht kennen

        parent::__construct($id);
    }

    public function isValid()
    {
        if ($this->task->jenkins->use_jenkins !== true) {
            return true;
        }

        $url = $this->task->jenkins->getApi() . '/job/' . $this->name . '/api/json';

        @file_get_contents($url);

        return strpos($http_response_header[0], '200 OK') !== false;
    }

    public function execute($path_user, $callback_url, $handin_file_id = null)
    {
        if (!$this->isValid()) {
            throw new \BadMethodCallException(_('Keine Verbindung zum Jenkins oder Job falsch konfiguriert'));
        }

        $callback_url = str_replace(' ', '%20', $callback_url);

        /**
         * curl -vX POST http://localhost:8080/job/test42/build
         * --form file0=@/Users/stephan/test/user.zip
         * --form file1=@/Users/stephan/test/config.zip
         * --form json='{"parameter": [{"name":"user.zip", "file":"file0"},{"name":"config.zip", "file":"file1"}]}'

         */

        #$path_user = get_upload_file_path($handin_file->document->id);

        $path_config = get_upload_file_path($this->file->id);

        $jobBuildData = array(
            'token' => md5(uniqid('aylüh', true)),
            'job_id' => $this->id,
            'handin_file_id' => $handin_file_id
        );

        $jobBuild = JobBuild::create($jobBuildData);

        $data = array(
            'file0' => '@' . realpath($path_user),
            'file1' => '@' . realpath($path_config),
            'json' => json_encode(array('parameter' => array(
                array('name' => 'user.zip', 'file' => 'file0'),
                array('name' => 'config.zip', 'file' => 'file1'),
                array('name' => 'id', 'value' => $this->id),
                array('name' => 'token', 'value' => $jobBuild->token),
                array('name' => 'url', 'value' => $callback_url . '/callback.php')
            )))
        );

        $url = $this->task->jenkins->getApi() . '/job/' . $this->name . '/build';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new RuntimeException(sprintf(_('Error trying to launch job') . ' "%s"'), $this->name);
        }

        #print_r($server_output);
    }

    public function getAnalyticResult($buildnumber)
    {
        # http://build.bognari.me/job/java_upload/9/analysisResult/api/json?pretty=true&depth=1

        $c = @file_get_contents($this->task->jenkins->getApi() . '/job/' . $this->name . '/' . $buildnumber . '/analysisResult/api/json?pretty=true&depth=1');

        if (is_string($c)) {#preg_match("/\\" : \\".*java_cs.*\//U", $input_line, $output_array);
            #/var/lib/jenkins/workspace/java_cs@2/src/Kampf1.java
            $c = preg_replace('/\"[^\"]*' . $this->name . '[^\"]*\//U', '"', $c);

            $json = json_decode($c);

            $data = array(
                'numberOfWarnings' => $json->numberOfWarnings,
                'warnings' => $json->warnings
            );

            $c = json_encode($data);
        }

        return $c;
    }

    public function getTestResult($buildnumber)
    {
        # http://build.bognari.me/job/java_upload/9/testReport/api/json?pretty=true&depth=1

        $c = @file_get_contents($this->task->jenkins->getApi() . '/job/' . $this->name . '/' . $buildnumber . '/testReport/api/json?pretty=true&depth=1');

        if (is_string($c)) {
            $c = preg_replace('/\"[^\"]*' . $this->name . '[^\"]*\//U', '"', $c);

            $json = json_decode($c);

            $data = array(
                'failCount' => $json->failCount,
                'suites' => $json->suites
            );

            $c = json_encode($data);
        }
        return $c;
    }

    public function isSuccessfull($buildnumber)
    {
        $c = @file_get_contents($this->task->jenkins->getApi() . '/job/' . $this->name . '/' . $buildnumber . '/api/json?depth=1');

        if ($c === null) {
            return false;
        }

        $data = json_decode($c);

        return $data->result !== 'FAILURE';
    }

    public static function analyticCmp($a, $b)
    {
        return ($a->primaryLineNumber < $b->primaryLineNumber) ? -1 : 1;
    }
}