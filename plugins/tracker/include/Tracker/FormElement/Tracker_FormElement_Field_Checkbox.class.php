<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Tracker_FormElement_Field_MultiSelectbox.class.php');

class Tracker_FormElement_Field_Checkbox extends Tracker_FormElement_Field_MultiSelectbox {
    
    /**
     * @return boolean
     */
    public function isMultiple() {
        return false;
    }
    
    
    protected function _fetchField($id, $name, $selected_values, $submitted_values = array()) {
        $html = '';


        $html .= '<ul style="list-style-type:none;padding-left:5px; margin-top: 2px;"';
        if ($id) {
            $html .= 'id="'. $id .'" ';
        }
        if ($name) {
            $html .= 'name="'. $name .'" ';
        }
        $html .= '>';
 
        if (($submitted_values) && !is_array($submitted_values)) {
            $submitted_values_array[] = $submitted_values;
            $submitted_values = $submitted_values_array;
        }
        
        foreach($this->getBind()->getAllValues() as $id => $value) {
            $transition_id = null;
            if ($this->isTransitionValid($from, $value)) {
                $transition_id = $this->getTransitionId($from, $value->getId());
                if (!empty($submitted_values)) {
                    $checked = in_array($id, array_values($submitted_values)) ? 'checked="checked"' : '';
                    
                } else {
                    $checked = isset($selected_values[$id]) ? 'checked="checked"' : '';
                }
                if ($this->userCanMakeTransition($transition_id)) {
                    if (!$value->isHidden()) {
                        $style = $this->getBind()->getSelectOptionInlineStyle($id);
                        $html .= '<li><input type="checkbox" name="'. $id .'" id=cb_'. $id .' '. $checked .'/>';
                        $html .= '<label for="cb_'. $id .'" >'.$this->getBind()->formatArtifactValue($id) .'</label>';
                        $html .= '</li>';
                    }
                }
            }
        }
        
        $html .= '</ul>';
        return $html;
    }//*/
    
    
    /**
     * @return the label of the field (mainly used in admin part)
     */
    public static function getFactoryLabel() {
        return $GLOBALS['Language']->getText('plugin_tracker_formelement_admin', 'checkbox');
    }

    /**
     * @return the description of the field (mainly used in admin part)
     */
    public static function getFactoryDescription() {
        return $GLOBALS['Language']->getText('plugin_tracker_formelement_admin','checkbox_desc');
    }
    
    /**
     * @return the path to the icon
     */
    public static function getFactoryIconUseIt() {
        return $GLOBALS['HTML']->getImagePath('ic/ui-check-box.png');
    }
    
    /**
     * @return the path to the icon
     */
    public static function getFactoryIconCreate() {
        return $GLOBALS['HTML']->getImagePath('ic/ui-check--plus.png');
    }

    /**
     * Change the type of the multi select box
     * @param string $type the new type
     *
     * @return boolean true if the change is allowed and successful
     */
    public function changeType($type) {
        if ($type === 'sb' || $type === 'msb') {
            // We should remove the entry in msb table
            // However we keep it for the case where admin changes its mind.
            return true;
        }
        return false;
    }
    
    ///////////////////////////////////////////////
    //////////////TODO/////////////////////////////
    ///////////////////////////////////////////////
    protected function _fetchFieldMasschange($id, $name, $selected_values) { 
        $html = '';
        $multiple = ' ';
        $size     = ' ';
        if ($this->isMultiple()) {
            $multiple = ' multiple="multiple" ';
            $size     = ' size="'. min($this->getMaxSize(), count($this->getBind()->getAllValues()) + 2) .'" ';
            if ($name) {
                $name .= '[]';
            }
        }
        $html .= '<select ';
        if ($id) {
            $html .= 'id="'. $id .'" ';
        }
        if ($name) {
            $html .= 'name="'. $name .'" ';
        }
        $html .= $size . $multiple .'>';      

        //if ( $this->fieldHasEnableWorkflow() ) {
        $html .= '<option value="'.$GLOBALS['Language']->getText('global','unchanged').'" selected="selected">'. $GLOBALS['Language']->getText('global','unchanged') .'</option>';
        $html .= '<option value="100">'. $GLOBALS['Language']->getText('global','none') .'</option>';
        //}

        foreach($this->getBind()->getAllValues() as $id => $value) {
                    if (!$value->isHidden()) {
                        $style = $this->getBind()->getSelectOptionInlineStyle($id);
                        $html .= '<option value="'. $id .'" style="'. $style .'">';
                        $html .= $this->getBind()->formatArtifactValue($id);
                        $html .= '</option>';
                    }
        }

        $html .= '</select>';
        return $html;
    }
    
    
}
?>
