<?php

namespace block_bacs_attendance_list;

class attendance_list {

    public $course;
    public $contests;
    public $students;

    public $contest_id_list;
    public $student_id_list;

    public $last_modifier;
    public $time_modified;

    public function __construct($course_id) {
        global $DB;

        $now = time();

        $this->course = $DB->get_record('course', array('id' => $course_id));
        $this->contests = $DB->get_records('bacs', array('course' => $course_id), 'starttime ASC', '*');

        $role = $DB->get_record('role', array('shortname' => 'student'));
        $context_instance = \context_course::instance($course_id);
        $coursestudents = get_role_users($role->id, $context_instance);
        $this->students = array();
        foreach ($coursestudents as $cur_student) {
            $this->students[] = $DB->get_record('user', array('id' => $cur_student->id), 'id, firstname, lastname', MUST_EXIST);
        }

        $attendance_list_info = $DB->get_record("block_bacs_attendance_list", ["course_id" => $this->course->id]);
        if ($attendance_list_info) {
            $modified_content = json_decode($attendance_list_info->content, true);

            $this->last_modifier = $DB->get_record('user', array('id' => $attendance_list_info->last_modifier));
            $this->time_modified = $attendance_list_info->time_modified;
        } else {
            $modified_content = array();
        
            $this->last_modifier = false;
            $this->time_modified = 0;
        }

        $status_not_started = array('short' => 'до', 'long' => 'Контест не начался', 'color' => '#ccf');

        $status_absent      = array('short' => '-',    'long' => 'Отсутствовал на занятии',  'color' => '#fcc');
        $status_running     = array('short' => 'идет', 'long' => 'Контест идет',             'color' => '#ffc');
        $status_presolved   = array('short' => 'пре',  'long' => 'Только предпрорешивание',  'color' => '#ffc');
        $status_present     = array('short' => '+',    'long' => 'Присутствовал на занятии', 'color' => '#cfc');

        $this->student_id_list = array();
        foreach ($this->students as $cur_student) {$this->student_id_list[] = $cur_student->id;}

        $this->contest_id_list = array();
        foreach ($this->contests as $cur_contest) {$this->contest_id_list[] = $cur_contest->id;}

        foreach ($this->students as $cur_student) {
            $cur_student->contest_info = array();

            foreach ($this->contests as $cur_contest) {
                if (array_key_exists("$cur_contest->id-$cur_student->id", $modified_content)) {
                    $cur_student->contest_info[$cur_contest->id] = 
                        $modified_content["$cur_contest->id-$cur_student->id"];
                    continue;
                }

                if ($cur_contest->starttime > $now) {
                    $cur_student->contest_info[$cur_contest->id] = $status_not_started;
                    continue;
                }

                if ($cur_contest->endtime > $now) {
                    $cur_status = $status_running;
                } else {
                    $cur_status = $status_absent;
                }

                $contest_submits = json_decode($cur_contest->standings);
                if ( !is_array($contest_submits)) $contest_submits = array();

                foreach ($contest_submits as $cur_submit) {

                    if ($cur_submit->user_id != $cur_student->id) continue;
                    if ($cur_submit->submit_time >= $cur_contest->endtime) continue;
                    
                    if ($cur_submit->submit_time >= $cur_contest->starttime) {
                        $cur_status = $status_present;
                        break;
                    }

                    $cur_status = $status_presolved;
                }

                $cur_student->contest_info[$cur_contest->id] = $cur_status;
            }
        }
    }
}