<?php
/**
 * Jenkins
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
 * @property null|\SimpleORMapCollection|string jenkins_url
 * @property null|\SimpleORMapCollection|string jenkins_token
 * @property null|\SimpleORMapCollection|string jenkins_user
 */
class Jenkins extends \Leeroy_SimpleORMap
{
    /**
     * creates new task, sets up relations
     *
     * @param string $id
     */
    public function __construct($id = null)
    {
        $this->db_table = 'leeroy_config';

        parent::__construct($id);
    }

    public function getAPI()
    {
        if ($this->use_ssl) {
            return "https://" . $this->jenkins_user . ":" . $this->jenkins_token . "@" . $this->jenkins_url;
        }
        return "http://" . $this->jenkins_user . ":" . $this->jenkins_token . "@" . $this->jenkins_url;
    }

    public function getURL()
    {
        if ($this->use_ssl) {
            return "https://" . $this->jenkins_url;
        }
        return "http://" . $this->jenkins_url;
    }

    public function isConnected()
    {
        if ($this->use_jenkins != "1") {
            return true;
        }

        @file_get_contents($this->getAPI());

        if (strpos($http_response_header[0], "200 OK") !== false) {
            return true;
        }
        return false;
    }


}
