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
 * Block bacsstat is defined here.
 *
 * @package     block_bacsstat
 * @copyright   2025 Aleksey Chipiga<yxaz16@yandex.ru>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_bacsstat extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_bacsstat');
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
        

        
        $bacs_contests = $DB->get_records('bacs', [], null, 'id,name,course');

        $bacs_stats = array_reduce($bacs_contests, function($carry, $contest) {
            global $DB, $USER;

            $course = isset($carry[$contest->course]) ? $carry[$contest->course] : $DB->get_record('course', ['id' => $contest->course], 'id,fullname');
            $submits = $DB->get_records('bacs_submits', ['user_id' => $USER->id, 'contest_id'=> $contest->id], null, 'id,points');
            $contest->complete_count = count(array_filter($submits, function($item) {
                return $item->points == '100';
            }));
            $contest->total_tasks = $DB->count_records('bacs_tasks_to_contests', ['contest_id' => $contest->id]);
            $contest->complete_percents = round($contest->complete_count == 0 ? 0 : 100 / ($contest->total_tasks / $contest->complete_count), 2);
            if(isset($course->contests)) {
                array_push($course->contests, $contest); 
            } else {
                $course->contests = [$contest];
            }
            $carry[$course->id] = $course;

            return $carry;
        }, []);

        // Add logic here to define your template data or any other content.
        $data = ['bacs_stats' => array_values($bacs_stats)];

        $this->content->text = $OUTPUT->render_from_template('block_bacsstat/content', $data);


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
            $this->title = get_string('pluginname', 'block_bacsstat');
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
            'my' => true
        );
    }
}
