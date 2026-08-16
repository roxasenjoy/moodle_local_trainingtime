<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
  'local_clicksws_get_user_course_clicks' => [
    'classname'   => 'local_clicksws\\external\\clicks',
    'methodname'  => 'get_user_course_clicks',
    'classpath'   => '',
    'description' => 'Return activity view clicks for a user in a course or ALL within a date range.',
    'type'        => 'read',
    'capabilities'=> 'report/log:view',
    'ajax'        => false,
  ],
];

$services = [
  'ClicksWS Service' => [
    'functions' => ['local_clicksws_get_user_course_clicks'],
    'restrictedusers' => 0,
    'enabled' => 1,
    'shortname' => 'clicksws_service'
  ]
];
