<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
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
 *
 */

namespace Tuleap\Mediawiki\Migration;

use DataAccess;
use DataAccessObject;

class MoveToCentralDbDao extends DataAccessObject
{
    /**
     * @var DataAccess
     */
    private $central_database_name;

    public function __construct($central_database_name)
    {
        parent::__construct();
        $this->enableExceptionsOnError();
        $this->central_database_name = $central_database_name;
    }

    /**
     * @return bool
     */
    public function testDatabaseAvailability()
    {
        if (trim($this->central_database_name) === '') {
            return false;
        }
        return $this->retrieveCount('SHOW DATABASES LIKE '.$this->da->quoteSmart($this->central_database_name)) !== 0;
    }

    public function move($project_id)
    {
        $project_id            = $this->da->escapeInt($project_id);
        $current_database_name = $this->getCurrentDatabase($project_id);

        if ($current_database_name != $this->central_database_name) {
            $this->moveTables($project_id, $current_database_name, $this->central_database_name);

            $this->updateUsedDatabase($project_id, $this->central_database_name);
        }
    }

    private function getCurrentDatabase($project_id)
    {
        $sql = "SELECT * FROM plugin_mediawiki_database WHERE project_id = $project_id";
        $row = $this->retrieveFirstRow($sql);
        return $row['database_name'];
    }

    private function moveTables($project_id, $database_name)
    {
        $dar = $this->retrieve("SHOW TABLES FROM $database_name");
        foreach ($dar as $row) {
            $table_name = array_pop($row);
            $new_table_name = 'mw_' . $project_id . '_' . substr($table_name, 2);
            $sql = sprintf(
                'ALTER TABLE `%s`.`%s` RENAME `%s`.`%s`',
                $database_name,
                $table_name,
                $this->central_database_name,
                $new_table_name
            );
            $this->update($sql);
        }
    }

    private function updateUsedDatabase($project_id)
    {
        $central_database_name = $this->da->quoteSmart($this->central_database_name);
        $this->update("UPDATE plugin_mediawiki_database SET database_name = $central_database_name WHERE project_id = $project_id");
    }
}