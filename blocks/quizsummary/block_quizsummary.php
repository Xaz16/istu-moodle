<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block quizsummary is defined here.
 *
 * @package     block_quizsummary
 * @copyright   2025 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_quizsummary extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_quizsummary');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $OUTPUT, $DB, $USER;
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        

        // $user = $DB->get_record('user', array('id' => $userid));
        
        $quiz_attempts = $DB->get_records('quiz_attempts', ['userid' => $USER->id]);

        $courses_map = array_reduce($quiz_attempts, function($carry, $attempt) {
            global $DB;
            $quiz_id = $attempt->quiz;
            $quiz = $DB->get_record('quiz', ['id' => $quiz_id]);
            $course_id = $quiz->course;
            $quiz_index = 0;
            if(!$carry[$course_id]) {
                $carry[$course_id] = $DB->get_record('course', ['id' => $course_id]);
            }
            if(!$carry[$course_id]->quizes) {
                $carry[$course_id]->quizes = [$quiz];

            } else {
                array_push($carry[$course_id]->quizes, $quiz);
                $quiz_index = count($carry[$course_id]->quizes) - 1;
            }

            $time_finish = new DateTime(gmdate("Y-m-d\TH:i:s\Z", $attempt->timefinish));
            $attempt->spent_time = $time_finish->diff(new DateTime(gmdate("Y-m-d\TH:i:s\Z", $attempt->timestart)))->format("%H:%I:%S");

            if(!$carry[$course_id]->quizes[$quiz_index]->attempts_done) {
                $carry[$course_id]->quizes[$quiz_index]->attempts_done = [$attempt];
            } else {
                array_push($carry[$course_id]->quizes[$quiz_index]->attempts_done, $attempt);
            }

            return $carry;
        }, []);


        // Add logic here to define your template data or any other content.
        $data = ["courses" => array_values($courses_map)];

        $this->content->text = $OUTPUT->render_from_template('block_quizsummary/content', $data);


        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_quizsummary');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array(
            'my' => true,
        );
    }
}
