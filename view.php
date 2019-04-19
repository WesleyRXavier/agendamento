<?php


require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require (dirname(__FILE__) . '/lib.php');
require ("$CFG->libdir/pdflib.php");
require './classes/print_html.class.php';
require './classes/model_print_html.class.php';
require './classes/button.class.php';
require_once($CFG->dirroot . '/lib/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);
$view = optional_param('view', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('agendamento', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $agendamento = $DB->get_record('agendamento', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $agendamento = $DB->get_record('agendamento', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $agendamento->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('agendamento', $agendamento->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context_course = context_course::instance($course->id);

$event = \mod_agendamento\event\course_module_viewed::create(array(
            'objectid' => $PAGE->cm->instance,
            'context' => $PAGE->context,
        ));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $agendamento);
$event->trigger();

$PAGE->set_url('/mod/agendamento/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($agendamento->name));
$PAGE->set_heading(format_string($course->fullname));
$modcontext = context_module::instance($cm->id);

define('VIEW_URL_LINK', "./view.php?id=" . $id);
define('VIEW_INIT_URL_LINK', $CFG->wwwroot . "/course/view.php?id=" . $course->id);

echo $OUTPUT->header();
$model = new model_print_html();
//
?>
<form class="form-horizontal">
<fieldset>

<!-- Form Name -->
<legend>Form Name</legend>

<!-- Password input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="passwordinput">Password Input</label>
  <div class="col-md-4">
    <input id="passwordinput" name="passwordinput" type="password" placeholder="placeholder" class="form-control input-md">
    <span class="help-block">help</span>
  </div>
</div>

<!-- Search input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="searchinput">Search Input</label>
  <div class="col-md-4">
    <input id="searchinput" name="searchinput" type="search" placeholder="placeholder" class="form-control input-md">
    <p class="help-block">help</p>
  </div>
</div>

<!-- Prepended text-->
<div class="form-group">
  <label class="col-md-4 control-label" for="prependedtext">Prepended Text</label>
  <div class="col-md-4">
    <div class="input-group">
      <span class="input-group-addon">prepend</span>
      <input id="prependedtext" name="prependedtext" class="form-control" placeholder="placeholder" type="text">
    </div>
    <p class="help-block">help</p>
  </div>
</div>

<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="textinput">Text Input</label>  
  <div class="col-md-4">
  <input id="textinput" name="textinput" type="text" placeholder="placeholder" class="form-control input-md">
  <span class="help-block">help</span>  
  </div>
</div>

</fieldset>
</form>
<?

//
$link_voltar = html_writer::start_tag('a', array('href' => VIEW_INIT_URL_LINK, 'style' => 'margin-bottom:3%; margin-left:25%;'));
$link_voltar .= get_string('voltar', 'agendamento');
$link_voltar .= html_writer::end_tag('a');
echo $link_voltar;

echo $OUTPUT->footer();
