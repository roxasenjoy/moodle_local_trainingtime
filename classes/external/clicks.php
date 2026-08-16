<?php
namespace local_clicksws\external;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_system;

class clicks extends external_api {
  public static function get_user_course_clicks_parameters() {
    return new external_function_parameters([
      'userid'   => new external_value(PARAM_INT, 'User ID', VALUE_REQUIRED),
      'courseid' => new external_value(PARAM_RAW, 'Course ID or "ALL"', VALUE_REQUIRED),
      'tstart'   => new external_value(PARAM_INT, 'Start unix time', VALUE_DEFAULT, 0),
      'tend'     => new external_value(PARAM_INT, 'End unix time', VALUE_DEFAULT, 0),
    ]);
  }

  public static function get_user_course_clicks($userid, $courseid, $tstart=0, $tend=0) {
    global $DB;
    self::validate_context(context_system::instance());
    require_capability('report/log:view', context_system::instance());

    $params = ['userid'=>(int)$userid, 'evname'=>'%course_module_viewed%'];
    $where = "userid = :userid AND action = 'viewed' AND (target = 'course_module' OR eventname LIKE :evname)";

    if (!empty($tstart)) { $where .= " AND timecreated >= :tstart"; $params['tstart']=(int)$tstart; }
    if (!empty($tend))   { $where .= " AND timecreated <= :tend";   $params['tend']  =(int)$tend; }

    if (strtolower($courseid) !== 'all') {
      $where .= " AND courseid = :courseid";
      $params['courseid'] = (int)$courseid;
    }

    $sql = "SELECT timecreated, eventname, action, target, courseid, contextinstanceid AS cmid, component, objecttable
              FROM {logstore_standard_log}
             WHERE $where
          ORDER BY timecreated ASC, id ASC";
    $recs = $DB->get_records_sql($sql, $params);

    $out = [];
    foreach ($recs as $r) {
      $out[] = [
        'timecreated' => (int)$r->timecreated,
        'eventname'   => (string)$r->eventname,
        'action'      => (string)$r->action,
        'target'      => (string)$r->target,
        'courseid'    => (int)$r->courseid,
        'cmid'        => (int)$r->cmid,
        'component'   => (string)$r->component,
        'objecttable' => (string)$r->objecttable,
      ];
    }
    return $out;
  }

  public static function get_user_course_clicks_returns() {
    return new external_multiple_structure(new external_single_structure([
      'timecreated' => new external_value(PARAM_INT,  'Unix time'),
      'eventname'   => new external_value(PARAM_TEXT, 'Event name'),
      'action'      => new external_value(PARAM_TEXT, 'Action'),
      'target'      => new external_value(PARAM_TEXT, 'Target'),
      'courseid'    => new external_value(PARAM_INT,  'Course ID'),
      'cmid'        => new external_value(PARAM_INT,  'Course module id'),
      'component'   => new external_value(PARAM_TEXT, 'Component'),
      'objecttable' => new external_value(PARAM_TEXT, 'Object table'),
    ]));
  }
}
