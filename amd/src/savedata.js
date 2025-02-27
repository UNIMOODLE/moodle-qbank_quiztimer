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

define(function() {
    "use strict";

    function save(textElementId, dropdownElementId) {
        var textElement = document.getElementById(textElementId);
        var dropdownElement = document.getElementById(dropdownElementId);

        textElement.oninput = function() {

            handleInput(textElement, dropdownElement);
        };

        dropdownElement.onchange = function() {

            handleInput(textElement, dropdownElement);
        };


        function handleInput(textElement, dropdownElement) {
            var timeValue = textElement.value;
            var dropdownValue = dropdownElement.value;

            if (dropdownValue === 'm') {
                timeValue *= 60;
            } else if (dropdownValue === 'h') {
                timeValue *= 3600;
            }

            var questionId = textElementId.split('-')[1];

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                }
            };
            xhr.send('time=' + encodeURIComponent(timeValue) + '&questionId=' +
                encodeURIComponent(questionId) + '&dropdown_option=' + encodeURIComponent(dropdownValue));
        }
    }

    return {
        save: save
    };
});
