<?php
/**
 *
 *
 * Long description for file (if any)...
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Stephan Mielke
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

$content = array(array(
    'kategorie' => _('Aktionen'),
    'eintrag' => array()));

$infobox = array('picture' => 'infobox/schedules.jpg', 'content' => $content);
?>

<?= $this->render_partial('index/_breadcrumb', array('path' => array('overview', array('student/view_student/' . $task->getId(), $task['title']), array('index/analytics_reload/' . $data->getId(), _('Warte auf Analyseergebnisse'))))) ?>

<?= MessageBox::info(sprintf(_('Die Analyse wird durchgeführt, bitte haben Sie etwas geduld. %sJetzt neu laden %s oder %s10%s Sekunden warten'),
    '<a href="' . $controller->url_for('index/show_analytics_handin/' . $handin_id . '/true') . '">', '</a>', '<span id="spnSeconds">', '</span>')); ?>

<script>
    $(document).ready(function () {
        window.setInterval(function () {
            var iTimeRemaining = $("#spnSeconds").html();
            iTimeRemaining = eval(iTimeRemaining);
            if (iTimeRemaining == 0) {
                window.location.href = "<?= $controller->url_for('index/show_analytics/' . $handin_id . '/true') ?>";
            }
            else {
                $("#spnSeconds").html(iTimeRemaining - 1);
            }
        }, 1000);
    });
</script>