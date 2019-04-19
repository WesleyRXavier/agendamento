<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Example constant, you probably want to remove this :-)
 */
define('agendamento_ULTIMATE_ANSWER', 42);

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function agendamento_supports($feature) {

    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: 
            return true;
        case FEATURE_COMPLETION_HAS_RULES: 
            return true;
        default:
            return null;
    }
}

/**
 * Obtains the automatic completion state for this forum based on any conditions
 * in forum settings.
 *
 * @global object
 * @global object
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not. (If no conditions, then return
 *   value depends on comparison type)
 */
function agendamento_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get forum details
    if (!($terms = $DB->get_record('agendamento', array('id' => $cm->instance)))) {
        throw new Exception("Can't find forum {$cm->instance}");
    }

    if ($terms->completionsubmit) {
        return $terms->completionsubmit <= $DB->get_field_sql("
 SELECT 
     COUNT(1) 
 FROM 
     {agendamento_accept}
 WHERE
     userid=:userid AND instance=:instance", array('userid' => $userid, 'instance' => $terms->id));
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}

/**
 * Saves a new instance of the agendamento into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $agendamento Submitted data from the form in mod_form.php
 * @param mod_agendamento_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted agendamento record
 */
function agendamento_add_instance(stdClass $agendamento, mod_agendamento_mod_form $mform = null) {
    global $DB;

    $agendamento->timecreated = time();

    // You may have to add extra stuff in here.

    $agendamento->id = $DB->insert_record('agendamento', $agendamento);

    agendamento_grade_item_update($agendamento);

    return $agendamento->id;
}

/**
 * Updates an instance of the agendamento in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $agendamento An object from the form in mod_form.php
 * @param mod_agendamento_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function agendamento_update_instance(stdClass $agendamento, mod_agendamento_mod_form $mform = null) {
    global $DB;

    $agendamento->timemodified = time();
    $agendamento->id = $agendamento->instance;

    // You may have to add extra stuff in here.

    $result = $DB->update_record('agendamento', $agendamento);

    agendamento_grade_item_update($agendamento);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every agendamento event in the site is checked, else
 * only agendamento events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function agendamento_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$agendamentos = $DB->get_records('agendamento')) {
            return true;
        }
    } else {
        if (!$agendamentos = $DB->get_records('agendamento', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($agendamentos as $agendamento) {
        // Create a function such as the one below to deal with updating calendar events.
        // agendamento_update_events($agendamento);
    }

    return true;
}

/**
 * Removes an instance of the agendamento from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function agendamento_delete_instance($id) {
    global $DB;

    if (!$agendamento = $DB->get_record('agendamento', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('agendamento', array('id' => $agendamento->id));

    agendamento_grade_item_delete($agendamento);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $agendamento The agendamento instance record
 * @return stdClass|null
 */
function agendamento_user_outline($course, $user, $mod, $agendamento) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $agendamento the module instance record
 */
function agendamento_user_complete($course, $user, $mod, $agendamento) {
    
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in agendamento activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function agendamento_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link agendamento_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function agendamento_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
    
}

/**
 * Prints single activity item prepared by {@link agendamento_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function agendamento_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function agendamento_cron() {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function agendamento_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of agendamento?
 *
 * This function returns if a scale is being used by one agendamento
 * if it has support for grading and scales.
 *
 * @param int $agendamentoid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given agendamento instance
 */
function agendamento_scale_used($agendamentoid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('agendamento', array('id' => $agendamentoid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of agendamento.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any agendamento instance
 */
function agendamento_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('agendamento', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given agendamento instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $agendamento instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function agendamento_grade_item_update(stdClass $agendamento, $reset = false) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($agendamento->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($agendamento->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax'] = $agendamento->grade;
        $item['grademin'] = 0;
    } else if ($agendamento->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid'] = -$agendamento->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/agendamento', $agendamento->course, 'mod', 'agendamento', $agendamento->id, 0, null, $item);
}

/**
 * Delete grade item for given agendamento instance
 *
 * @param stdClass $agendamento instance object
 * @return grade_item
 */
function agendamento_grade_item_delete($agendamento) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/agendamento', $agendamento->course, 'mod', 'agendamento', $agendamento->id, 0, null, array('deleted' => 1));
}

/**
 * Update agendamento grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $agendamento instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function agendamento_update_grades(stdClass $agendamento, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/agendamento', $agendamento->course, 'mod', 'agendamento', $agendamento->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function agendamento_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for agendamento file areas
 *
 * @package mod_agendamento
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function agendamento_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the agendamento file areas
 *
 * @package mod_agendamento
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the agendamento's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function agendamento_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding agendamento nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the agendamento module instance
 * @param stdClass $course current course record
 * @param stdClass $module current agendamento instance record
 * @param cm_info $cm course module information
 */
function agendamento_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Extends the settings navigation with the agendamento settings
 *
 * This function is called when the context for the page is a agendamento module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $agendamentonode agendamento administration node
 */
function agendamento_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $agendamentonode = null) {
    // TODO Delete this function and its docblock, or implement it.
}
