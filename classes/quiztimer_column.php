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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Display information about all the mod_quiztimer modules in the requested course. *
 * @package qbank_quiztimer
 * @copyright 2023 Proyecto UNIMOODLE
 * @author UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qbank_quiztimer;

use core_question\local\bank\column_base;
use moodle_url;
use stdClass;

/**
 * Class to add an action column to the question table.
 */
class quiztimer_column extends column_base {

    /**
     * Retrieves the name.
     *
     * @return string
     */
    public function get_name(): string {
        return 'questionstatus';
    }

    /**
     * Get the title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('questionstatus', 'qbank_quiztimer');
    }

    /**
     * Display the content of the column.
     *
     * @param object $question The question object.
     * @param array  $rowclasses The row classes.
     */
    protected function display_content($question, $rowclasses): void {
        global $USER, $DB, $questionid, $timequestion, $PAGE;
        $timequestion = $DB->get_records('question_timer');
        $attributes = [];

        if (question_has_capability_on($question, 'edit')) {

            $time = optional_param('time', null, PARAM_INT);
            $questionId = optional_param('questionId', null, PARAM_INT);
            $dropdownOption = optional_param('dropdown_option', null, PARAM_TEXT);

            if ($time !== null && $questionId !== null ) {
                if ($questionId == $question->id) {
                    $questionid = $DB->get_field('question_versions', 'questionbankentryid',
                    ['questionid' => $question->id], IGNORE_MISSING);
                    $existingdata = $DB->get_record('question_timer', ['questionid' => $questionid]);
                    $data = new stdClass;

                    $data->id = $existingdata->id;
                    $data->questionid = $questionid;
                    $data->time = $time;
                    $data->unit_time = $dropdownOption;
                    $data->modifierid = $USER->id;
                    $data->timemodified = time();
                    $DB->update_record('question_timer', $data);
                    return;
                }
            }

            // Get the question ID to use it as the index.
            $questionid = $DB->get_field('question_versions', 'questionbankentryid',
            ['questionid' => $question->id], IGNORE_MISSING);
            $existingdata = $DB->get_record('question_timer', ['questionid' => $questionid]);

            if ($existingdata) {
                if (is_object($existingdata)) {
                    $data = new stdClass;
                    $data->id = $existingdata->id;
                    $id = $data->id;
                    $number = intval($id);
                } else {
                    $data = null;
                    $number = 0; // Assign a default value to $number.
                }
            } else {
                $data = new stdClass;
                $data->questionid = $DB->get_field('question_versions', 'questionbankentryid',
                    ['questionid' => $question->id], IGNORE_MISSING);
                $number = $questionid;
                $data->modifierid = $USER->id;
                $data->timemodified = time();
                if ($time !== null) {
                    $data->time = $time;
                } else {
                    $data->time = get_config('qbank_quiztimer', 'time'); // Default value for "time".
                }
                if ($dropdownOption !== null) {
                    $data->unit_time = $dropdownOption;
                } else {
                    $data->unit_time = get_config('qbank_quiztimer', 'time_unit'); // Default value for "unit_time".
                }
                $DB->insert_record('question_timer', $data);
            }

            $currenturl = $_SERVER['REQUEST_URI'];
            $allowedurl = '/question/edit.php';

            if (strpos($currenturl, $allowedurl) !== true) {
                if (question_has_capability_on($question, 'edit')) {
                    $html = '<div style="display: flex; align-items: center;">';
                    if (isset($timequestion[intval($number)])) {
                        // Existing time question.
                        $timeValue = $timequestion[intval($number)]->time;
                        $unitTime = $timequestion[intval($number)]->unit_time;
                        if ($unitTime === 's') {
                            $timeValueDisplay = $timeValue;
                        } else if ($unitTime === 'm') {
                            $timeValueDisplay = $timeValue / 60;
                        } else if ($unitTime === 'h') {
                            $timeValueDisplay = $timeValue / 3600;
                        } else {
                            $timeValueDisplay = $timeValue; // Default.
                        }
                    } else {
                        // Default time.
                        $timeValueDisplay = get_config('qbank_quiztimer', 'time');
                    }

                    // Input field.
                    $html .= '<input type="number" value="' . $timeValueDisplay . '" name="time" id="text-' . $question->id . '" class="form-control" style="width: 75px; margin-right: 5px;">';

                    // Dropdown menu.
                    $options = [
                        ['name' => '', 'value' => 'd'],
                        ['name' => get_string('seconds', 'qbank_quiztimer'), 'value' => 's'],
                        ['name' => get_string('minutes', 'qbank_quiztimer'), 'value' => 'm'],
                        ['name' => get_string('hours', 'qbank_quiztimer'), 'value' => 'h'],
                    ];

                    $dropdown = '<select name="dropdown_option" class="custom-select my-2" id="timedropdown-' . $question->id . '" style="margin-left: 5px;">';

                    foreach ($options as $option) {
                        $name = $option['name'];
                        $value = $option['value'];
                        if (isset($timequestion[intval($number)]) && $timequestion[intval($number)] != null) {
                            $selected = ($value === $timequestion[intval($number)]->unit_time) ? 'selected' : '';
                            $dropdown .= "<option value=\"$value\" $selected>$name</option>";
                        } else {
                            // Check if $value is 'default' to set it as selected.
                            $selected = ($value === get_config('qbank_quiztimer', 'time_unit')) ? 'selected' : '';
                            $dropdown .= "<option value=\"$value\" $selected>$name</option>";
                        }
                    }
                    $dropdown .= '</select>';

                    $html .= $dropdown;
                    $html .= '</div>';

                    // Display the HTML.
                    echo $html;

                    // Include JavaScript file.
                    $PAGE->requires->js_call_amd('qbank_quiztimer/savedata', 'save', ['text-' . $question->id, 'timedropdown-' . $question->id]);
                }
            }
        }

    }

    /**
     * Get the extra classes associated with the element.
     *
     * @return array
     */
    public function get_extra_classes(): array {
        return ['pr-3'];
    }
}
