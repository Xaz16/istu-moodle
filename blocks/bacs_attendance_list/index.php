<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/bacs/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/bacs/utils.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/bacs/submit_verdicts.php');

require_once(dirname(__FILE__).'/utils.php');

require_login();

$CONTEXT_SYS = context_system::instance();

/// Print the page header

$PAGE->set_url('/blocks/bacs_attendance_list/index.php', array());
$PAGE->set_title('Журнал посещаемости');
$PAGE->set_heading('Журнал посещаемости');
//$PAGE->set_context($CONTEXT);

// Output starts here
echo $OUTPUT->header();

$PAGE->navbar->ignore_active();
$PAGE->navbar->add('preview', new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add('name of thing', new moodle_url('/a/link/if/you/want/one.php'));

// HEADER END BOOTSTRAP
if (is_admin()) {
    $my_courses = all_courses_full_info();
} else {
    $my_courses = my_courses_full_info();
}

print "<p><a href='/teacher_monitor.php'><button class='btn btn-info'>Назад к монитору учителя</button></a></p>";


//<script>document.getElementById('textbox').focus()</script><br />
$filter_text = '';  
print "<form action='' method='get'>  
Поиск по названию курса: <br>
<p><input type='text' name='filter' value=$filter_text>
<input type='submit' value='Найти'/></p>
</form>";
$name_to_compare = mb_strtolower(optional_param('filter', '', PARAM_TEXT));
if(strlen($name_to_compare) > 0) {
    print "<table class='generaltable' style='width: auto;'>
    <thead>    
        <th> Поиск по запросу " . $name_to_compare . "</th>
    </thead>
    <tbody>";
}
print "<table class='generaltable' style='width: auto;'>
    <thead>    
        <th> N </th>
        <th> ID </th>
        <th> Курс </th>
    </thead>
    <tbody>";

$idx = 0;
$n_found = 0;
foreach ($my_courses as $cur_course) {
    $idx += 1;
    $pos = strpos(mb_strtolower($cur_course->fullname), $name_to_compare);
    $is_need_to_show = 1;
    if($pos === False and strlen($name_to_compare) > 0) {
        $is_need_to_show = 0;
    }
    if($is_need_to_show) {
        $n_found++;
    print "<tr>
        <td>$idx</td>
        <td>$cur_course->id</td>
        <td><a href='/blocks/bacs_attendance_list/view.php?id=$cur_course->id'> $cur_course->fullname </a></td>
    </tr>";
    }
}

print "</tbody></table>";
if($n_found == 0) {
    print "<th>
        Нет результатов
    </th>";
}
echo $OUTPUT->footer();
