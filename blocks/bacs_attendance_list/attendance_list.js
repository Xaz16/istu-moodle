class AttendanceList {
    constructor (student_ids, contest_ids) {
        this.student_ids = student_ids;
        this.contest_ids = contest_ids;

        console.log(student_ids);
        console.log(contest_ids);

        this.main_table     = document.getElementById("attendance_list_table");
        this.main_form      = document.getElementById("make_change_form");
        this.help_label     = document.getElementById("how_to_edit_help_label");
        this.form_templates = document.getElementById("form_templates");

        this.form_elements = {
            student_id : document.getElementById("form_student_id"),
            contest_id : document.getElementById("form_contest_id"),

            student_name : document.getElementById("form_student_name"),
            contest_name : document.getElementById("form_contest_name"),
            short_text   : document.getElementById("form_short_text"),
            long_text    : document.getElementById("form_long_text"),
            color        : document.getElementById("form_color"),
        };

    }

    color_rgb_to_hex(rgb) {
        // Choose correct separator
        let sep = rgb.indexOf(",") > -1 ? "," : " ";
        // Turn "rgb(r,g,b)" into [r,g,b]
        rgb = rgb.substr(4).split(")")[0].split(sep);

        let r = (+rgb[0]).toString(16),
            g = (+rgb[1]).toString(16),
            b = (+rgb[2]).toString(16);

        if (r.length == 1)
            r = "0" + r;
        if (g.length == 1)
            g = "0" + g;
        if (b.length == 1)
            b = "0" + b;

        return "#" + r + g + b;
    }

    select(row_index, col_index) {    
        /// prepare and show form
        var selected_cell = this.main_table.rows[row_index + 2].cells[col_index + 1];
        var student_name = this.main_table.rows[row_index + 2].cells[0].innerText;
        var contest_name = this.main_table.rows[0].cells[col_index + 1].innerText;
        var student_id = this.student_ids[row_index];
        var contest_id = this.contest_ids[col_index];

        var fe = this.form_elements;

        fe.student_id.value = student_id;
        fe.contest_id.value = contest_id;
        
        fe.student_name.innerHTML = student_name;
        fe.contest_name.innerHTML = contest_name;
        fe.short_text.value = selected_cell.innerHTML;
        fe.long_text.value = selected_cell.title;
        fe.color.value = this.color_rgb_to_hex(selected_cell.style.backgroundColor);

        this.help_label.style.display = 'none';
        this.main_form.style.display = 'block';
        this.form_templates.style.display = 'block';

        /// move table cell selection
        for (var cur_row_index = 0; cur_row_index < this.student_ids.length; cur_row_index++) {
            for (var cur_col_index = 0; cur_col_index < this.contest_ids.length; cur_col_index++) {
                var cell = this.main_table.rows[cur_row_index + 2].cells[cur_col_index + 1];
                
                cell.style.border = '';
            }
        }

        selected_cell.style.border = '2px green solid';
    }

    set_template_absent() {
        var fe = this.form_elements;
    
        fe.short_text.value = "-";
        fe.long_text.value = "Отсутствовал на занятии";
        fe.color.value = "#ffcccc";
    }

    set_template_present() {
        var fe = this.form_elements;
    
        fe.short_text.value = "+";
        fe.long_text.value = "Присутствовал на занятии";
        fe.color.value = "#ccffcc";
    }

}
