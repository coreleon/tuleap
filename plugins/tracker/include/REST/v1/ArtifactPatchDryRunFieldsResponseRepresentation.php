<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Tracker\REST\v1;

use Tracker_FormElement_Field;
use Tuleap\Tracker\Action\Move\FeedbackFieldCollector;
use Tuleap\Tracker\REST\MinimalFieldRepresentation;

class ArtifactPatchDryRunFieldsResponseRepresentation
{

    /**
     * @var array {@type MinimalFieldRepresentation}
     */
    public $fields_migrated = [];

    /**
     * @var array {@type MinimalFieldRepresentation}
     */
    public $fields_not_migrated = [];

    /**
     * @var array {@type MinimalFieldRepresentation}
     */
    public $fields_partially_migrated = [];

    public function build(FeedbackFieldCollector $feedback_field_collector)
    {
        foreach ($feedback_field_collector->getFieldsNotMigrated() as $field) {
            $this->fields_not_migrated[] = $this->getFieldRepresentation($field);
        }

        foreach ($feedback_field_collector->getFieldsFullyMigrated() as $field) {
            $this->fields_migrated[] = $this->getFieldRepresentation($field);
        }

        foreach ($feedback_field_collector->getFieldsPartiallyMigrated() as $field) {
            $this->fields_partially_migrated[] = $this->getFieldRepresentation($field);
        }
    }

    /**
     * @return MinimalFieldRepresentation
     */
    private function getFieldRepresentation(Tracker_FormElement_Field $field)
    {
        $field_representation = new MinimalFieldRepresentation();
        $field_representation->build($field);

        return $field_representation;
    }
}