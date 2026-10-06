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

namespace local_wizard;

use advanced_testcase;
use local_wizard\local\wizard\skill_registry;
use local_wizard\local\wizard\wizard\skills\explain_docs_skill;
use local_wizard\local\wizard\services\lookup\docs_embeddings_gate;

/**
 * Tests for the documentation skill gate.
 *
 * @package    local_wizard
 * @copyright  2026 Wunderbyte GmbH <info@wunderbyte.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_wizard\local\wizard\services\lookup\docs_embeddings_gate
 */
final class docs_embeddings_gate_test extends advanced_testcase {
    /**
     * Without any config, the skill defaults to off.
     */
    public function test_default_off(): void {
        $this->resetAfterTest();
        $this->assertFalse(docs_embeddings_gate::is_docs_skill_active());
    }

    /**
     * The system-wide enable-all flag activates the skill.
     */
    public function test_enable_all(): void {
        $this->resetAfterTest();
        set_config('aiskillenableall', 1, 'local_wizard');
        $this->assertTrue(docs_embeddings_gate::is_docs_skill_active());
    }

    /**
     * The per-skill toggle activates and deactivates the skill independently.
     */
    public function test_per_skill_toggle(): void {
        $this->resetAfterTest();
        $setting = skill_registry::get_skill_toggle_setting_name(explain_docs_skill::SKILL_NAME);

        set_config($setting, 1, 'local_wizard');
        $this->assertTrue(docs_embeddings_gate::is_docs_skill_active());

        set_config($setting, 0, 'local_wizard');
        $this->assertFalse(docs_embeddings_gate::is_docs_skill_active());
    }

    /**
     * Enable-all wins even when the per-skill toggle is off.
     */
    public function test_enable_all_overrides_per_skill_off(): void {
        $this->resetAfterTest();
        $setting = skill_registry::get_skill_toggle_setting_name(explain_docs_skill::SKILL_NAME);
        set_config($setting, 0, 'local_wizard');
        set_config('aiskillenableall', 1, 'local_wizard');
        $this->assertTrue(docs_embeddings_gate::is_docs_skill_active());
    }
}
