<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/bacs/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/bacs/utils.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/bacs/submit_verdicts.php');

require_once(dirname(__FILE__).'/utils.php');

require_login();

$query_course_id = optional_param('id', 0, PARAM_INT);

if ($query_course_id == 0) {
    print("Course id parameter is required");
    die();
}

$course_context = context_course::instance($query_course_id);
$has_capability_view = has_capability('mod/bacs:view', $course_context);
$has_capability_edit = has_capability('mod/bacs:addinstance', $course_context);

/// Print the page header

$PAGE->set_url('/blocks/bacs_attendance_list/view.php', ['id' => $query_course_id]);
$PAGE->set_title('Журнал посещаемости');
$PAGE->set_heading('Журнал посещаемости');
//$PAGE->set_context($course_context);

if ( !$has_capability_view) {
    print("You have no permission for this operation!");
    die();
}

// Output starts here
echo $OUTPUT->header();

//print_contest_title();

$PAGE->navbar->ignore_active();
$PAGE->navbar->add('preview', new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add('name of thing', new moodle_url('/a/link/if/you/want/one.php'));

// HEADER END BOOTSTRAP

print "<p><a href='index.php'><button class='btn btn-info'>Назад к списку курсов</button></a></p>";

$attendance_list = new \block_bacs_attendance_list\attendance_list($query_course_id);

/// print

print "<h3>" . $attendance_list->course->fullname . "</h3>";

print "<table id='attendance_list_table' class='generaltable' style='width: auto;'>
    <thead>";

print "<tr>";
print "<th></th>";
foreach ($attendance_list->contests as $cur_contest) {
    print "<th style='text-align: center;'><div style='writing-mode: vertical-lr; text-orientation: mixed;'>" . $cur_contest->name . "</div></th>";
}
print "</tr>";

print "<tr>
        <th style='text-align: right;'>Студент</th>";
foreach ($attendance_list->contests as $cur_contest) {
    print "<th style='text-align: center;'>" . date("d M", $cur_contest->starttime) . "</th>";
}
print "</tr>";

print "</thead>";
print "<tbody>";

$row_index = -1;
foreach ($attendance_list->students as $cur_student) {
    $row_index += 1;

    print "<tr>";

    print "<td style='text-align: right;'> $cur_student->firstname $cur_student->lastname </td>";
    
    $col_index = -1;
    foreach ($attendance_list->contests as $cur_contest) {
        $col_index += 1;

        $cur_status = (object)$cur_student->contest_info[$cur_contest->id];
        
        $long_text  = htmlspecialchars($cur_status->long);
        $short_text = htmlspecialchars($cur_status->short);

        if ($has_capability_edit) {
            $onclick_action = "attendance_list.select($row_index, $col_index);";
        } else {
            $onclick_action = "";
        }

        print "<td 
                style='background-color: $cur_status->color; text-align: center; cursor: pointer;' 
                title='$long_text'
                onclick='$onclick_action'>";
        print $short_text;
        print "</td>";
    }

    print "</tr>";
}

print "</tbody>";
print "</table>";

echo $OUTPUT->download_dataformat_selector(
    'Скачать таблицу как', 
    'download.php', 
    'dataformat', 
    array('id' => $attendance_list->course->id)
);

$contest_id_list_json = json_encode($attendance_list->contest_id_list);
$student_id_list_json = json_encode($attendance_list->student_id_list);

$last_modifier = $attendance_list->last_modifier;
if ($last_modifier) {
    $last_modification_html = "<p>
        Последнее изменение сделано <a target='_blank' href='/user/profile.php?id=$last_modifier->id'>
        $last_modifier->firstname $last_modifier->lastname
        </a>
        в ". userdate($attendance_list->time_modified,"[%H:%M:%S] %d %B %Y") ."
    </p>";
} else {
    $last_modification_html = "";
}

if ( !$has_capability_edit) {
    print "<div>
            <h3>Изменение записи в журнале</h3>
            $last_modification_html
            <span id='how_to_edit_help_label'>У вас нет прав на редактирование значений в журнале.</span>
        </div>";
} else {
    print "<div>
        <h3>Изменение записи в журнале</h3>
        $last_modification_html
        <span id='how_to_edit_help_label'>Для редактирования значений в журнале кликните по ячейке таблицы.</span>

        <p id='form_templates' style='display: none;'>
            <button class='btn btn-success' onclick='attendance_list.set_template_present();'>Шаблон: присутствовал</button>
            <button class='btn btn-danger' onclick='attendance_list.set_template_absent();'>Шаблон: отсутствовал</button>
        </p>

        <form
            id='make_change_form' 
            enctype='multipart/form-data' 
            action='make_changes.php' 
            method='POST' 
            style='display: none;'
        >

        <p>Имя студента: <span id='form_student_name'></span></p>
        <p>Название контеста: <span id='form_contest_name'></span></p>
        <p>Текст ячейки: <input id='form_short_text' name='short' type='text' size=30></p>
        <p>Текст подсказки: <input id='form_long_text' name='long' type='text' size=30></p>
        <p>Цвет ячейки: <input id='form_color' name='color' type='color'></p>
        
        <input id='form_student_id' name='student_id' type='hidden'>
        <input id='form_contest_id' name='contest_id' type='hidden'>

        <input class='btn btn-info' type='submit' value='Сохранить изменения'>

        </form>
    </div>";

    print "<script type='text/javascript' src='attendance_list.js'></script>";
    print "<script type='text/javascript'>
        var attendance_list = new AttendanceList(
            $student_id_list_json,
            $contest_id_list_json
        );
    </script>";
}

echo $OUTPUT->footer();
