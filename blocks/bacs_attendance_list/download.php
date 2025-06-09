<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib/dataformatlib.php');
require_once(dirname(__FILE__).'/utils.php');

$query_course_id = required_param('id', PARAM_INT);
$dataformat = optional_param('dataformat', '', PARAM_ALPHA);

$PAGE->set_url('/blocks/bacs_attendance_list/download.php', array("id" => $query_course_id));
$PAGE->set_context(context_system::instance());

require_login();

if ($query_course_id == 0) {
    print("Course id parameter is required");
    die();
}

$course_context = context_course::instance($query_course_id);
$has_capability_view = has_capability('mod/bacs:view', $course_context);
$has_capability_edit = has_capability('mod/bacs:addinstance', $course_context);

if ( !$has_capability_view) {
    print("You have no permission for this operation!");
    die();
}

$attendance_list = new \block_bacs_attendance_list\attendance_list($query_course_id);

$columns = array(
    'student' => 'Имя студента',
);

foreach ($attendance_list->contests as $cur_contest) {
    $columns[$cur_contest->id] = $cur_contest->name;
}

$dataset = array();
foreach ($attendance_list->students as $cur_student) {
    $dataset_entry = ['student' => "$cur_student->firstname $cur_student->lastname"];

    foreach ($cur_student->contest_info as $contest_id => $contest_info) {
        $dataset_entry[$contest_id] = $contest_info['short'];
    }

    $dataset[] = $dataset_entry;
}

$filename = "Журнал посещаемости " . $attendance_list->course->fullname;

download_as_dataformat(
    $filename,
    $dataformat,
    $columns,
    (new ArrayObject($dataset))->getIterator()
);

