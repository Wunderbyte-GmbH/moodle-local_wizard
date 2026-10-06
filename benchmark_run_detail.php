<?php
// This file is part of Moodle - http://moodle.org/
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

/**
 * Benchmark run detail page.
 *
 * @package    local_wizard
 * @copyright  2026 Wunderbyte GmbH <info@wunderbyte.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

use local_wizard\local\wizard\benchmark\benchmark_metrics_calculator;

$id = required_param('id', PARAM_INT);

$run = $DB->get_record('local_wizard_bm_runs', ['id' => $id], '*', MUST_EXIST);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/wizard/benchmark_run_detail.php', ['id' => $id]));
$PAGE->set_title(get_string('benchmark_run_detail_title', 'local_wizard', $id));
$PAGE->set_heading(get_string('benchmark_run_detail_heading', 'local_wizard', (object)[
    'id' => $id,
    'label' => htmlspecialchars($run->label),
]));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

$scenarios = $DB->get_records('local_wizard_bm_scenarios', ['run_id' => $id], 'scenario_key ASC');
$metrics   = $DB->get_records('local_wizard_bm_metrics', ['run_id' => $id], 'metric_key ASC');

$calc       = new benchmark_metrics_calculator();
$thresholds = $calc->get_thresholds();
$metricsmap = [];
foreach ($metrics as $m) {
    if ($m->scenario_class === null) {
        $metricsmap[$m->metric_key] = [
            'value' => (float)$m->metric_value,
            'unit'  => $m->metric_unit,
        ];
    }
}

$backurl = new moodle_url('/local/wizard/benchmark_report.php');

echo html_writer::tag(
    'p',
    html_writer::link($backurl, get_string('benchmark_back_all_runs', 'local_wizard'), ['class' => 'btn btn-secondary'])
);

// Run header.
echo html_writer::start_div('card mb-3');
echo html_writer::start_div('card-body');

// Baseline Pin action in header card!
if (!$run->is_baseline) {
    echo html_writer::start_tag('form', [
        'method' => 'post',
        'action' => new moodle_url('/local/wizard/benchmark_report.php'),
        'class' => 'form-inline float-right',
    ]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'pinbaseline']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'runid', 'value' => $run->id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    echo html_writer::empty_tag('input', [
        'type' => 'text',
        'name' => 'baselinelabel',
        'value' => htmlspecialchars($run->label),
        'class' => 'form-control form-control-sm mr-2',
        'placeholder' => get_string('benchmark_baseline_label', 'local_wizard'),
    ]);
    echo html_writer::empty_tag('input', [
        'type' => 'submit',
        'value' => get_string('benchmark_pin_baseline', 'local_wizard'),
        'class' => 'btn btn-sm btn-warning',
    ]);
    echo html_writer::end_tag('form');
} else {
    echo html_writer::div(
        html_writer::span(
            get_string('benchmark_baseline_label', 'local_wizard') . ': ' . htmlspecialchars($run->label),
            'badge badge-primary float-right'
        )
    );
}

echo html_writer::start_tag('dl', ['class' => 'row mb-0']);
$fields = [
    get_string('benchmark_run_uuid', 'local_wizard')   => $run->run_uuid,
    get_string('benchmark_model', 'local_wizard')      => $run->model_id,
    get_string('benchmark_skill_set', 'local_wizard')   => $run->skill_set,
    get_string('benchmark_env', 'local_wizard')        => $run->environment,
    get_string('benchmark_git_ref', 'local_wizard')    => $run->git_ref,
    get_string('benchmark_date', 'local_wizard')       => userdate($run->timecreated, '%d.%m.%Y %H:%M'),
    get_string('benchmark_duration', 'local_wizard')   => number_format($run->duration_ms / 1000, 2) . 's',
    get_string('benchmark_tokens', 'local_wizard')     => number_format((int)$run->total_tokens),
];
foreach ($fields as $k => $v) {
    echo html_writer::tag('dt', $k, ['class' => 'col-sm-3']);
    echo html_writer::tag('dd', htmlspecialchars((string)$v), ['class' => 'col-sm-9']);
}
echo html_writer::end_tag('dl');
echo html_writer::end_div(); // Card-body.
echo html_writer::end_div(); // Card.

// Metric summary.
echo html_writer::tag('h3', get_string('benchmark_metrics', 'local_wizard'));

$table = new html_table();
$table->head = [
    get_string('benchmark_metric', 'local_wizard'),
    get_string('benchmark_value', 'local_wizard'),
    get_string('benchmark_threshold', 'local_wizard'),
    get_string('benchmark_status', 'local_wizard'),
];
$table->attributes['class'] = 'table table-sm table-bordered';
$table->data = [];

foreach ($metricsmap as $key => $data) {
    $val = $data['value'];
    $unit = $data['unit'];
    $threshold = $thresholds[$key] ?? null;
    $status = $threshold === null ? '' : ($val >= $threshold ? '✅' : ($val >= $threshold * 0.95 ? '⚠️' : '❌'));

    $unitstr = '';
    if ($unit === 'percent') {
        $unitstr = '%';
    } else if ($unit === 'ms') {
        $unitstr = 'ms';
    } else if ($unit === 'tokens') {
        $unitstr = '';
    }

    $table->data[] = [
        $key,
        "{$val}{$unitstr}",
        $threshold !== null ? "{$threshold}%" : '—',
        $status,
    ];
}
echo html_writer::table($table);

// Scenario results.
echo html_writer::tag('h3', get_string('benchmark_scenario_results', 'local_wizard', count($scenarios)));

$filter = optional_param('filter', '', PARAM_ALPHA);
echo html_writer::start_tag('p');
echo html_writer::link($PAGE->url, get_string('benchmark_filter_all', 'local_wizard'), [
    'class' => 'btn btn-xs ' . ($filter === 'failed' ? 'btn-outline-secondary' : 'btn-secondary'),
]) . ' ';
echo html_writer::link(
    new moodle_url($PAGE->url, ['filter' => 'failed']),
    get_string('benchmark_filter_failed', 'local_wizard'),
    [
        'class' => 'btn btn-xs ' . ($filter === 'failed' ? 'btn-danger' : 'btn-outline-danger'),
    ]
);
echo html_writer::end_tag('p');

$table = new html_table();
$table->head = [
    get_string('benchmark_id', 'local_wizard'),
    get_string('benchmark_class', 'local_wizard'),
    get_string('benchmark_pass', 'local_wizard'),
    get_string('benchmark_rt_expected', 'local_wizard'),
    get_string('benchmark_rt_actual', 'local_wizard'),
    get_string('benchmark_skill_expected', 'local_wizard'),
    get_string('benchmark_skill_selected', 'local_wizard'),
    get_string('benchmark_json_valid', 'local_wizard'),
    get_string('benchmark_contract', 'local_wizard'),
    get_string('benchmark_planned', 'local_wizard'),
    get_string('benchmark_duration_ms', 'local_wizard'),
    get_string('benchmark_error', 'local_wizard'),
];
$table->attributes['class'] = 'table table-sm generaltable';
$table->data = [];

foreach ($scenarios as $s) {
    if ($filter === 'failed' && $s->passed) {
        continue;
    }
    $rowclass = $s->passed ? '' : 'table-danger';
    $pass     = $s->passed ? '✅' : '❌';
    $json     = $s->json_valid ? '✅' : '❌';
    $contract = $s->contract_compliant ? '✅' : '❌';
    $planned  = $s->planned_steps_present ? '✅' : '—';

    $row = new html_table_row([
        html_writer::tag('small', htmlspecialchars($s->scenario_key)),
        html_writer::tag('small', $s->scenario_class),
        $pass,
        html_writer::tag('small', htmlspecialchars($s->response_type_expected)),
        html_writer::tag('small', htmlspecialchars($s->response_type_actual)),
        html_writer::tag('small', htmlspecialchars($s->skill_expected)),
        html_writer::tag('small', htmlspecialchars($s->skill_selected)),
        $json,
        $contract,
        $planned,
        $s->duration_ms,
        html_writer::tag('small', htmlspecialchars((string)$s->error_message), ['style' => 'color:red']),
    ]);
    if ($rowclass) {
        $row->attributes['class'] = $rowclass;
    }
    $table->data[] = $row;
}
echo html_writer::table($table);

echo $OUTPUT->footer();
