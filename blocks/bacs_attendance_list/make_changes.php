<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/bacs/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/bacs/utils.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/bacs/submit_verdicts.php');

require_once(dirname(__FILE__).'/utils.php');
require_login();

$updated_cell_obj = [
    "short"      => $_POST["short"],
    "long"       => $_POST["long"],
    "color"      => $_POST["color"],
    "student_id" => $_POST["student_id"],
    "contest_id" => $_POST["contest_id"],
];

$student_id = $updated_cell_obj["student_id"];
$contest_id = $updated_cell_obj["contest_id"];

$contest = $DB->get_record("bacs", ["id" => $contest_id], "*", MUST_EXIST);

$course_id = $contest->course;

$course_context = context_course::instance($course_id);
$has_capability_view = has_capability('mod/bacs:view', $course_context);
$has_capability_edit = has_capability('mod/bacs:addinstance', $course_context);

/// Print the page header

$PAGE->set_url('/blocks/bacs_attendance_list/view.php', ['id' => $course_id]);
$PAGE->set_title('Журнал посещаемости');
$PAGE->set_heading('Журнал посещаемости');
//$PAGE->set_context($course_context);

if ( !$has_capability_edit) {
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

if ( !$DB->record_exists("block_bacs_attendance_list", ["course_id" => $course_id])) {
    $new_record = new stdClass();

    $new_record->course_id = $course_id;
    $new_record->content = "{}";
    $new_record->time_modified = time();
    $new_record->last_modifier = $USER->id;
    
    $DB->insert_record("block_bacs_attendance_list", $new_record);
}

$updating_record = $DB->get_record("block_bacs_attendance_list", ["course_id" => $course_id]);

$decoded_content = json_decode($updating_record->content, true);

var_dump($decoded_content);

$decoded_content["$contest_id-$student_id"] = $updated_cell_obj;

$updating_record->content = json_encode($decoded_content);
$updating_record->time_modified = time();
$updating_record->last_modifier = $USER->id;

$DB->update_record("block_bacs_attendance_list", $updating_record);

// redirect
print "Successful update / Успешное обновление";
redirect_via_js("view.php?id=".$course_id);

var_dump($_POST);

echo $OUTPUT->footer();
