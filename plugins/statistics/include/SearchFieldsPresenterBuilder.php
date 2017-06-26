<?php
/**
 * Copyright (c) Enalean, 2016. All Rights Reserved.
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

namespace Tuleap\Statistics;

use DateInterval;
use DateTime;
use EventManager;
use Statistics_Event;

class SearchFieldsPresenterBuilder
{
    public function buildSearchFieldsForFrequencies(
        array $type_values,
        $filter_value,
        $start_date_value,
        $end_date_value
    ) {
        $type_options   = $this->getListOfTypeValuePresenter($type_values);
        $filter_options = $this->getListOfFilterValuePresenter($filter_value);

        return new FrequenciesSearchFieldsPresenter(
            $type_options,
            $filter_options,
            $start_date_value,
            $end_date_value
        );
    }

    public function buildSearchFieldsForServices(
        $selected_project,
        $services_with_selected,
        $group_by_date_with_selected,
        $start_date_value,
        $end_date_value,
        $relative_y_axis_value
    ) {
        $start_date_value = $this->getDate($start_date_value, 'Y-m-d', 'P1M');
        $end_date_value   = $this->getDate($end_date_value, 'Y-m-d');

        if ($start_date_value > $end_date_value) {
            throw new StartDateGreaterThanEndDateException();
        }

        return new DiskUsageServicesSearchFieldsPresenter(
            $selected_project,
            $services_with_selected,
            $this->buildUrlParamsForServices(
                $services_with_selected,
                $start_date_value,
                $end_date_value,
                $group_by_date_with_selected,
                $relative_y_axis_value
            ),
            $group_by_date_with_selected,
            $start_date_value,
            $end_date_value,
            $relative_y_axis_value
        );
    }

    public function buildSearchFieldsForProjects(
        $services_with_selected,
        $start_date_value,
        $end_date_value
    ) {
        $start_date_value = $this->getDate($start_date_value, 'Y-m-d', 'P1W');
        $end_date_value   = $this->getDate($end_date_value, 'Y-m-d');

        if ($start_date_value > $end_date_value) {
            throw new StartDateGreaterThanEndDateException();
        }

        return new DiskUsageProjectsSearchFieldsPresenter(
            $services_with_selected,
            $this->buildUrlParamsForProjects($services_with_selected, $start_date_value, $end_date_value),
            $start_date_value,
            $end_date_value
        );
    }

    public function buildSearchFieldsForUserDetails(
        $user_name_value,
        $group_by_value,
        $start_date_value,
        $end_date_value
    ) {
        $group_by_options = $this->getListOfGroupByValuePresenter($group_by_value);

        return new DiskUsageUserDetailsSearchFieldsPresenter(
            $user_name_value,
            $group_by_options,
            $start_date_value,
            $end_date_value
        );
    }

    private function buildUrlParamsForServices(
        array $service_values,
        $start_date_value,
        $end_date_value,
        $group_by_date_with_selected,
        $relative_y_axis_value
    ) {
        $params = array(
            'services' => array()
        );

        foreach ($service_values as $service) {
            if ($service['is_selected']) {
                $params['services'][] = $service['key'];
            }
        }

        $params['start_date'] = $start_date_value;
        $params['end_date']   = $end_date_value;

        if ($relative_y_axis_value) {
            $params['relative'] = $relative_y_axis_value;
        }

        $params['group_by'] = $this->getSelectedGroupByDate($group_by_date_with_selected);

        return $params;
    }

    private function buildUrlParamsForProjects(
        array $service_values,
        $start_date_value,
        $end_date_value
    ) {
        $params = array(
            'services' => array()
        );

        foreach ($service_values as $service) {
            if ($service['is_selected']) {
                $params['services'][] = $service['key'];
            }
        }

        $params['start_date'] = $start_date_value;
        $params['end_date']   = $end_date_value;

        return $params;
    }

    private function getSelectedGroupByDate(array $group_by_values)
    {
        foreach ($group_by_values as $group_by_value) {
            if ($group_by_value['is_selected']) {
                return $group_by_value['key'];
            }
        }

        return $group_by_values[0]['key'];
    }

    private function getListOfGroupByValuePresenter($group_by_value)
    {
        $all_group_by = array(
            'day'   => $GLOBALS['Language']->getText('plugin_statistics', 'day'),
            'week'  => $GLOBALS['Language']->getText('plugin_statistics', 'week'),
            'month' => $GLOBALS['Language']->getText('plugin_statistics', 'month'),
            'year'  => $GLOBALS['Language']->getText('plugin_statistics', 'year')
        );

        $group_by_options = array();

        foreach ($all_group_by as $group_by => $label) {
            $group_by_options[] = $this->getValuePresenter($group_by, array($group_by_value), $label);
        }

        return $group_by_options;
    }

    private function getListOfTypeValuePresenter(array $type_values)
    {
        $all_data = array(
            'session'   => $GLOBALS['Language']->getText('plugin_statistics', 'session_type'),
            'user'      => $GLOBALS['Language']->getText('plugin_statistics', 'user_type'),
            'forum'     => $GLOBALS['Language']->getText('plugin_statistics', 'forum_type'),
            'filedl'    => $GLOBALS['Language']->getText('plugin_statistics', 'filedl_type'),
            'file'      => $GLOBALS['Language']->getText('plugin_statistics', 'file_type'),
            'groups'    => $GLOBALS['Language']->getText('plugin_statistics', 'groups_type'),
            'wikidl'    => $GLOBALS['Language']->getText('plugin_statistics', 'wikidl_type'),
            'oartifact' => $GLOBALS['Language']->getText('plugin_statistics', 'oartifact_type'),
            'cartifact' => $GLOBALS['Language']->getText('plugin_statistics', 'cartifact_type'),
        );

        EventManager::instance()->processEvent(
            Statistics_Event::FREQUENCE_STAT_ENTRIES,
            array('entries' => &$all_data)
        );

        $type_options = array();

        foreach ($all_data as $type => $label) {
            $type_options[] = $this->getValuePresenter($type, $type_values, $label);
        }

        return $type_options;
    }

    private function getListOfFilterValuePresenter($filter_value)
    {
        $all_filter = array(
            'month'  => $GLOBALS['Language']->getText('plugin_statistics', 'frequencies_filter_group_month'),
            'day'    => $GLOBALS['Language']->getText('plugin_statistics', 'frequencies_filter_group_day'),
            'hour'   => $GLOBALS['Language']->getText('plugin_statistics', 'frequencies_filter_group_hour'),
            'month1' => $GLOBALS['Language']->getText('plugin_statistics', 'frequencies_filter_month'),
            'day1'   => $GLOBALS['Language']->getText('plugin_statistics', 'frequencies_filter_day'),
        );

        $filter_options = array();

        foreach ($all_filter as $filter => $label) {
            $filter_options[] = $this->getValuePresenter($filter, array($filter_value), $label);
        }

        return $filter_options;
    }

    private function getValuePresenter($value, array $selected_values, $label)
    {
        return array(
            'value'       => $value,
            'is_selected' => in_array($value, $selected_values),
            'label'       => $label
        );
    }

    private function getDate($date, $format, $sub_interval = null)
    {
        if (! $date) {
            $date_time = new DateTime();
            if ($sub_interval) {
                $date_time = $date_time->sub(new DateInterval($sub_interval));
            }
            $date = $date_time->format($format);
        }

        return $date;
    }
}
