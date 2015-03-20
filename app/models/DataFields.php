<?php

namespace Leeroy;

/**
 * Class DataFields
 * @package Leeroy
 * Diese Klasse stellt die Abfragemöglichkeiten für die Zusatzdaten zur Verfügung
 */
class DataFields
{

    private $data;
    private $rule;
    private $seminar_id;

    /**
     * Erzeugt ein Datafields Objekt, das Zusatzangaben und deren Konfiguration einer Veranstaltung beinhaltet
     * @param string $seminar_id
     * @return DataFields
     */
    public static function getDataFields($seminar_id)
    {
        return new DataFields($seminar_id);
    }

    private function __construct($seminar_id)
    {
        $this->rule = \AuxLockRules::getLockRuleBySemId($seminar_id);
        $this->seminar_id = $seminar_id;
        $this->data = $this->get_aux_data();
    }

    private function filterDatafields($entries)
    {
        $new_entries = array();
        if ($this->rule !== null) {
            foreach ($entries as $key => $val) {
                if ($this->rule['attributes'][$key] === '1') {
                    $new_entries[$key] = $val;
                }
            }
        }

        return $new_entries;
    }

    private function get_aux_data()
    {
        $entries[0] = $this->filterDatafields(\DataFieldStructure::getDataFieldStructures('usersemdata'));
        $entries[1] = $this->filterDatafields(\DataFieldStructure::getDataFieldStructures('user'));

        $entry_data = array();
        for ($i = 0; $i <= 1; $i++) {
            foreach ($entries[$i] as $id => $entry) {
                $header[$id] = $entry->getName();
                $entry_data[$id] = '';
            }
        }

        $semFields = $this->filterDataFields(\AuxLockRules::getSemFields());
        foreach ($semFields as $id => $name) {
            $header[$id] = $name;
            $entry_data[$id] = '';
        }

        $data = array();

        $query = "SELECT GROUP_CONCAT({$GLOBALS['_fullname_sql']['full']} SEPARATOR ', ')
                 FROM seminar_user
                 LEFT JOIN auth_user_md5 USING (user_id)
                 LEFT JOIN user_info USING (user_id)
                 WHERE seminar_user.status = 'dozent' AND seminar_user.Seminar_id = ?";
        $teachers = \DBManager::get()->prepare($query);

        $query = "SELECT *, seminare.VeranstaltungsNummer AS vanr, seminare.Name AS vatitle
              FROM seminar_user
              LEFT JOIN auth_user_md5 USING (user_id)
              LEFT JOIN seminare USING (Seminar_id)
              WHERE Seminar_id = ? AND seminar_user.status IN ('autor', 'user')";
        $statement = \DBManager::get()->prepare($query);
        $statement->execute(array($this->seminar_id));
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $data[$row['user_id']]['entry'] = $entry_data;
            $data[$row['user_id']]['fullname'] = $row['Vorname'] . ' ' . $row['Nachname'];
            $data[$row['user_id']]['username'] = $row['username'];

            $entries[0] = $this->filterDatafields(\DataFieldEntry::getDataFieldEntries(array($row['user_id'], $this->seminar_id), 'usersemdata'));
            $entries[1] = $this->filterDatafields(\DataFieldEntry::getDataFieldEntries($row['user_id'], 'user'));

            for ($i = 0; $i <= 1; $i++) {
                foreach ($entries[$i] as $id => $entry) {
                    $data[$row['user_id']]['entry'][$id] = $entry->getDisplayValue(false);
                }
            }

            foreach ($semFields as $key => $name) {
                if ($key === 'vadozent') {
                        $teachers->execute(array($this->seminar_id));
                        $vadozent = $teachers->fetchColumn();
                        $teachers->closeCursor();

                    $data[$row['user_id']]['entry'][$key] = $vadozent;
                } elseif ($key === 'vasemester') {
                        $vasemester = get_semester($this->seminar_id);
                    $data[$row['user_id']]['entry'][$key] = $vasemester;
                } else {
                    $data[$row['user_id']]['entry'][$key] = $row[$key];
                }
            }
        }

        $order = $this->rule['order'];
        asort($order, SORT_NUMERIC);

        $new_header = array();
        foreach ($order as $key => $dontcare) {
            if (array_key_exists($key, $header)) {
                $new_header[$key] = $header[$key];
            }
        }

        return array('aux' => $data, 'header' => $new_header);
    }

    /**
     * Gibt die Namen der Zusatzdaten als String Array zurück
     * @return string[]
     */
    public function getHeaders()
    {
        return $this->data['header'];
    }

    /**
     * Gibt die Zusatzdaten für einen Benutzer als Stringarray zurück
     * @param string $user_id
     * @return string[]
     */
    public function getUserAux($user_id)
    {
        return $this->data['aux'][$user_id]['entry'];
    }

    /**
     * Testet ob die Zusatzangaben eines Benutzers sich an die Vorgaben halten
     * @param string $user_id
     * @param string[] $regex Array der regulären Ausdrücke
     * @return bool
     */
    public function isValid($user_id, $regex)
    {
        $ret = true;

        foreach ($this->data['aux'][$user_id]['entry'] as $field => $value) {
            $ret = $ret && @preg_match('/^' . $regex->$field . '$/', $value);
        }

        return $ret;
    }
}