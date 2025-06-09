<?php

function is_admin() {
    global $DB, $USER;

    $systemcontext = context_system::instance();
    return has_capability('moodle/course:delete', $systemcontext);
}

function is_teacher($context) {
    global $USER, $DB;

    /*
    $teacher_role = $DB->get_record('role', array('shortname' => 'editingteacher'));
    $users = get_role_users($teacher_role->id, $context);

    foreach ($users as $cur_user) {
        if ($cur_user->id == $USER->id) return true;
    }
    return false;
    //*/

    return has_capability('mod/bacs:edit', $context);
}

function is_student($author, $course) {
    global $DB;

    $context_instance = context_course::instance($course->id);
    $student_role = $DB->get_record('role', array('shortname' => 'student'));
    $students = get_role_users($student_role->id, $context_instance);

    foreach ($students as $cur_user) {
        if ($cur_user->id == $author->id) return true;
    }
    return false;
}

function my_courses_full_info() {
    global $USER, $DB;

    $all_courses = $DB->get_records('course');
    $my_courses = array();
    foreach ($all_courses as $cur_course) {
        $context_instance = context_course::instance($cur_course->id);
        //print $cur_course->fullname . ' - ' . (is_teacher($context_instance)?'TRUE':'FALSE') . '<br>';
        if (is_enrolled($context_instance, $USER, "", true)) $my_courses[] = $cur_course;
    }

    return $my_courses;
}

function all_courses_full_info() {
    global $USER, $DB;

    $all_courses = $DB->get_records('course');

    return $all_courses;
}
