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
 * Trigger test for start date delay trigger.
 *
 * @package    lifecycletrigger_customfielddelay
 * @group      lifecycletrigger
 * @copyright  2020 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\trigger;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\processor;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

/**
 * Trigger test for start date delay trigger.
 *
 * @package    lifecycletrigger_customfielddelay
 * @group      lifecycletrigger
 * @copyright  2020 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_customfielddelay_testcase extends \advanced_testcase {

    /** @var $triggerinstance trigger_subplugin Instance of the trigger. */
    private $triggerinstance;

    /** @var $processor processor Instance of the lifecycle processor. */
    private $processor;

    public function setUp() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->processor = new processor();
        $this->triggerinstance = \tool_lifecycle_trigger_customfielddelay_generator::create_trigger_with_workflow();

        $fieldcategory = self::getDataGenerator()->create_custom_field_category(['name' => 'Other fields']);

        $customfield = ['shortname' => 'test', 'name' => 'Custom field', 'type' => 'date',
            'categoryid' => $fieldcategory->get('id')];
        $field = self::getDataGenerator()->create_custom_field($customfield);
    }

    /**
     * Tests if courses, which have a customfield date in the future are not triggered by this plugin.
     */
    public function test_young_course() {
        $customfieldvalue = ['shortname' => 'test', 'value' => time() + 1000000];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);

        $recordset = $this->processor->get_course_recordset([$this->triggerinstance], []);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'The course should not have been triggered');
    }

    /**
     * Tests if courses, which are older than the default of 190 days are triggered by this plugin.
     */
    public function test_old_course() {

        $customfieldvalue = ['shortname' => 'test', 'value' => time() - 1000000];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);

        $recordset = $this->processor->get_course_recordset([$this->triggerinstance], []);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'The course should have been triggered');
    }
}