<?php
class block_bacs_attendance_list extends block_base {
    public function init() {
        $this->title = "Журнал посещаемости";
    }
    
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
     
        $this->content = new stdClass;
        
        $this->content->text   = '<a href="/blocks/bacs_attendance_list/index.php">Перейти к журналу посещаемости</a>';
        $this->content->footer = '';
     
        return $this->content;
    }
}