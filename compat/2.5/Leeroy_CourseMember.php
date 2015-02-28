<?php

/**
 * Created by IntelliJ IDEA.
 * User: stephan
 * Date: 15.02.15
 * Time: 20:14
 */
class Leeroy_CourseMember extends CourseMember
{

    public function getGroups()
    {
        return GetGroupsByCourseAndUser($this->seminar_id, $this->user_id);
    }

    public static function getGroupsForCourse($seminar_id)
    {
        $participants = Leeroy_CourseMember::findByCourse($seminar_id);

        $group = array();
        $group_names = array();

        foreach ($participants as $user) {
            $gruppen = $user->getGroups();

            if ($user->status === 'autor' && count($gruppen) > 0) {

                foreach ($gruppen as $gruppen_id => $gruppen_name) {
                    if ($group[$gruppen_id] === null) {
                        $group[$gruppen_id] = array();
                    }

                    $group_names[$gruppen_id] = $gruppen_name;

                    array_push($group[$gruppen_id], $user);
                }

            }
        }

        return array('names' => $group_names, 'members' => $group);
    }

    public static function cmp($a, $b)
    {
        if (get_nachname($a->user_id) === get_nachname($b->user_id)) {
            return get_vorname($a->user_id) < get_vorname($b->user_id) ? -1 : 1;
        }

        return get_nachname($a->user_id) < get_nachname($b->user_id) ? -1 : 1;
    }
}