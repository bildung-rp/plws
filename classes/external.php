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
 * An appropriate file description goes here
 *
 * @package     local_plws
 * @copyright  2012 Yair Spielmann, Synergy Learning for Pädagogisches Landesinstitut Rheinland Pfalz
 *             2017 Patrick Liersch, Pädagogisches Landesinstitut
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_plws;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;

class external extends external_api {

    /**
     * Parameterdefinition for method "deleteFile"
     *
     * @return {object} external_function_parameters
     */
    public static function delete_file_parameters() {
        return new \external_function_parameters(
                array('fileid' => new \external_value(PARAM_TEXT, 'File ID'))
        );
    }

    /**
     * Method to delete File with the given File ID.
     *
     * @param {string} fileid
     * @return {bool} true/false
     * @throws {moodle_exception}
     */
    public static function delete_file($fileid) {
        global $DB, $CFG;

        $result = false;

        self::validate_parameters(self::delete_file_parameters(), array('fileid' => $fileid));

        $fs = get_file_storage();

        $fileinfo = array('itemid' => $fileid);

        $file = $fs->get_file_by_id($fileinfo['itemid']);

        if (!$result = $file->delete()) {
            throw new \moodle_exception('filenotfound');
        }

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_value BOOL
     */
    public static function delete_file_returns() {
        return new \external_value(PARAM_BOOL, 0);
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_userid_by_username_parameters() {
        return new \external_function_parameters(
                array('username' => new \external_value(PARAM_TEXT, 'The username'))
        );
    }

    /**
     * Searches and returns user id from username
     * @return id userid
     */
    public static function get_userid_by_username($username) {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_userid_by_username_parameters(), array('username' => $username));

        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new \moodle_exception('cannotviewprofile');
        }

        if (!$userid = $DB->get_field('user', 'id', array('username' => $username))) {
            throw new \moodle_exception('usernamedoesnotexist');
        }
        return $userid;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_userid_by_username_returns() {
        return new \external_value(PARAM_INT, 0);
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function role_get_capability_parameters() {
        return new \external_function_parameters(
                array(
            'roleid' => new \external_value(PARAM_INT, 'Role ID'),
            'capability' => new \external_value(PARAM_TEXT, 'Capability name (e.g. coursereport/log:view)'),
            'contextid' => new \external_value(PARAM_INT, 'Context ID'),
                )
        );
    }

    /**
     * Gets the capability for a role in context.
     * @return array
     */
    public static function role_get_capability($roleid, $capability, $contextid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::role_get_capability_parameters(), array('roleid' => $roleid, 'capability' => $capability, 'contextid' => $contextid));

        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('moodle/role:review', $context)) {
            throw new \moodle_exception('cannotviewroles');
        }

        $toreturn = array();
        /*if (!$context = context::instance_by_id($contextid)) {
            throw new \moodle_exception('contextidnotfound');
        } */
        if (!$capabilitydata = $DB->get_record('capabilities', array('name' => $capability))) {
            throw new \moodle_exception('capabilitynotfound');
        }
        if (!$DB->get_record('role', array('id' => $roleid))) {
            throw new \moodle_exception('rolenotfound');
        }
        if ($rolecap = $DB->get_record('role_capabilities', array('roleid' => $roleid, 'contextid' => $contextid, 'capability' => $capability))) {
            $permission = $rolecap->permission;
        } else {
            $permission = CAP_INHERIT;
        }
        $allpermissions = array(
            CAP_INHERIT => 'inherit',
            CAP_ALLOW => 'allow',
            CAP_PREVENT => 'prevent',
            CAP_PROHIBIT => 'prohibit',
        );
        if (!isset($allpermissions[$permission])) {
            throw new \moodle_exception('unknownpermissionid');
        }
        $toreturn['permission'] = $allpermissions[$permission];

        $toreturn['risks'] = array();
        $allrisks = get_all_risks();
        foreach ($allrisks as $type => $risk) {
            if ($risk & (int) $capabilitydata->riskbitmask) {
                $toreturn['risks'][] = $type;
            }
        }
        return $toreturn;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function role_get_capability_returns() {
        return new \external_single_structure(
                array(
            'permission' => new \external_value(PARAM_TEXT, 'permission'),
            'risks' => new \external_multiple_structure(new \external_value(PARAM_TEXT, '')),
                )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function role_set_capability_parameters() {
        return new \external_function_parameters(
                array(
            'roleid' => new \external_value(PARAM_INT, 'Role ID'),
            'capability' => new \external_value(PARAM_TEXT, 'Capability name (e.g. coursereport/log:view)'),
            'contextid' => new \external_value(PARAM_INT, 'Context ID'),
            'permission' => new \external_value(PARAM_TEXT, 'Permission name (not set, allow, prevent or prohibit)'),
                )
        );
    }

    /**
     * Sets an override role permission for a capability in a context
     * @return id userid
     */
    public static function role_set_capability($roleid, $capability, $contextid, $permission) {
        global $DB, $USER;

        $params = self::validate_parameters(self::role_set_capability_parameters(), array('roleid' => $roleid,
                    'capability' => $capability,
                    'contextid' => $contextid,
                    'permission' => $permission));
                    
        /*if (!has_capability('moodle/role:override', get_system_context())) {
            throw new \moodle_exception('cannoteditroles');
        } */
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('moodle/role:override', $context)) {
            throw new \moodle_exception('cannoteditroles');
        }                           
        
        $permissiontypes = array(
            'not set' => CAP_INHERIT,
            'inherit' => CAP_INHERIT,
            'allow' => CAP_ALLOW,
            'prevent' => CAP_PREVENT,
            'prohibit' => CAP_PROHIBIT,
        );
        $permission = strtolower($permission);
        if (!isset($permissiontypes[$permission])) {
            throw new \moodle_exception('unknownpermission');
        }
        $permissionid = $permissiontypes[$permission];
        if (!assign_capability($capability, $permissionid, $roleid, $contextid, true)) {
            echo false;
        }
        return true;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function role_set_capability_returns() {
        return new \external_value(PARAM_BOOL, 0);
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function roles_list_parameters() {
        return new \external_function_parameters(array());
    }

    /**
     * Returns list of roles
     * @return id userid
     */
    public static function roles_list() {
        global $DB, $USER;

        $params = self::validate_parameters(self::roles_list_parameters(), array());

        $roles = array();
        $roleobjs = get_all_roles();
        foreach ($roleobjs as $obj) {
            $roles[] = array('id' => $obj->id, 'shortname' => $obj->shortname);
        }
        return $roles;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function roles_list_returns() {
        return new \external_multiple_structure(
                new \external_single_structure(
                array(
            'id' => new \external_value(PARAM_TEXT, 'Role ID'),
            'shortname' => new \external_value(PARAM_TEXT, 'Role shortname'),
                )
                )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function user_assign_authentication_parameters() {
        return new \external_function_parameters(
                array(
            'userid' => new \external_value(PARAM_INT, 'User ID'),
            'auth' => new \external_value(PARAM_TEXT, 'Authentication shortname'),
                )
        );
    }

    /**
     * Assigns authentication to user
     * @return bool success
     */
    public static function user_assign_authentication($userid, $auth) {
        global $DB, $USER;

        $params = self::validate_parameters(self::user_assign_authentication_parameters(), array('userid' => $userid, 'auth' => $auth));
       
        /*
        if (!has_capability('moodle/user:update', get_system_context())) {
            throw new \moodle_exception('cannoteditroles');
        } */
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('moodle/user:update', $context)) {
            throw new \moodle_exception('cannoteditroles');
        }           
               
        if (!$user = $DB->get_record('user', array('id' => $userid))) {
            throw new \moodle_exception('usernotfound');
        }
 
        if (!exists_auth_plugin($auth)) {
            throw new \moodle_exception('authdoesnotexist');
        }
 
        //$toupdate = new stdClass();
        
        $toupdate->id = $userid;
        $toupdate->auth = $auth;
        $toupdate->timemodified = time();
 
        if (!$DB->update_record('user', $toupdate)) {
            return false;
        }
    
        $updateduser = $DB->get_record('user', array('id' => $userid));

        return true;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function user_assign_authentication_returns() {
        return new \external_value(PARAM_BOOL, false);
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_role_assignment_parameters() {
        return new \external_function_parameters(
                array(
            'fromrole' => new \external_value(PARAM_INT, 'Role assigning'),
            'torole' => new \external_value(PARAM_INT, 'Role assigned'),
                )
        );
    }

    /**
     * Returns whether fromrole is allowed to assign torole to users.
     * @return int 0 or 1 allowed or not
     */
    public static function get_role_assignment($fromrole, $torole) {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_role_assignment_parameters(), array('fromrole' => $fromrole, 'torole' => $torole));

        /*
        if (!has_capability('moodle/role:manage', get_system_context())) {
            throw new \moodle_exception('cannoteditroles');
        } */
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('moodle/role:manage', $context)) {
            throw new \moodle_exception('cannoteditroles');
        }           

        if (!$DB->get_record('role', array('id' => $fromrole))) {
            throw new \moodle_exception('fromrolenotexists');
        }
        if (!$DB->get_record('role', array('id' => $torole))) {
            throw new \moodle_exception('torolenotexists');
        }

        if ($DB->get_record('role_allow_assign', array('roleid' => $fromrole, 'allowassign' => $torole))) {
            return 1;
        }
        return 0;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_role_assignment_returns() {
        return new \external_value(PARAM_INT, -1);
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function set_role_assignment_parameters() {
        return new \external_function_parameters(
                array(
            'fromrole' => new \external_value(PARAM_INT, 'Role assigning'),
            'torole' => new \external_value(PARAM_INT, 'Role assigned'),
            'value' => new \external_value(PARAM_INT, 'Value to set (0 or 1)'),
                )
        );
    }

    /**
     * Sets fromrole allowed or not to assign torole to user
     * @return bool success
     */
    public static function set_role_assignment($fromrole, $torole, $value) {
        global $DB, $USER;

        $params = self::validate_parameters(self::set_role_assignment_parameters(), array('fromrole' => $fromrole, 'torole' => $torole, 'value' => $value));

        /*
        if (!has_capability('moodle/role:manage', get_system_context())) {
            throw new \moodle_exception('cannoteditroles');
        } */
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('moodle/role:manage', $context)) {
            throw new \moodle_exception('cannoteditroles');
        }           

        if (!$DB->get_record('role', array('id' => $fromrole))) {
            throw new \moodle_exception('fromrolenotexists');
        }
        if (!$DB->get_record('role', array('id' => $torole))) {
            throw new \moodle_exception('torolenotexists');
        }

        if ($value == 0) {
            if (!$DB->delete_records('role_allow_assign', array('roleid' => $fromrole, 'allowassign' => $torole))) {
                return false;
            }
        } else if ($value == 1) {
            if (!$DB->get_record('role_allow_assign', array('roleid' => $fromrole, 'allowassign' => $torole))) {
                //$toinsert = new stdClass();
                $toinsert->roleid = $fromrole;
                $toinsert->allowassign = $torole;
                if (!$DB->insert_record('role_allow_assign', $toinsert)) {
                    return false;
                }
            }
        } else {
            throw new \moodle_exception('incorrectvalue');
        }
        return true;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function set_role_assignment_returns() {
        return new \external_value(PARAM_BOOL, false);
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_role_switch_parameters() {
        return new \external_function_parameters(
                array(
            'fromrole' => new \external_value(PARAM_INT, 'Role switching'),
            'torole' => new \external_value(PARAM_INT, 'Role switched to'),
                )
        );
    }

    /**
     * Returns whether fromrole is allowed to switch to torole.
     * @return int 0 or 1 allowed or not
     */
    public static function get_role_switch($fromrole, $torole) {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_role_switch_parameters(), array('fromrole' => $fromrole, 'torole' => $torole));

        /*
        if (!has_capability('moodle/role:manage', get_system_context())) {
            throw new \moodle_exception('cannoteditroles');
        } */
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('moodle/role:manage', $context)) {
            throw new \moodle_exception('cannoteditroles');
        }           

        if (!$DB->get_record('role', array('id' => $fromrole))) {
            throw new \moodle_exception('fromrolenotexists');
        }
        if (!$DB->get_record('role', array('id' => $torole))) {
            throw new \moodle_exception('torolenotexists');
        }

        if ($DB->get_record('role_allow_switch', array('roleid' => $fromrole, 'allowswitch' => $torole))) {
            return 1;
        }
        return 0;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_role_switch_returns() {
        return new \external_value(PARAM_INT, -1);
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function set_role_switch_parameters() {
        return new \external_function_parameters(
                array(
            'fromrole' => new \external_value(PARAM_INT, 'Role switching'),
            'torole' => new \external_value(PARAM_INT, 'Role switched to'),
            'value' => new \external_value(PARAM_INT, 'Value to set (0 or 1)'),
                )
        );
    }

    /**
     * Sets fromrole allowed or not to switch to torole
     * @return bool success
     */
    public static function set_role_switch($fromrole, $torole, $value) {
        global $DB, $USER;

        $params = self::validate_parameters(self::set_role_switch_parameters(), array('fromrole' => $fromrole, 'torole' => $torole, 'value' => $value));

        /*
        if (!has_capability('moodle/role:manage', get_system_context())) {
            throw new \moodle_exception('cannoteditroles');
        } */
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        if (!has_capability('moodle/role:manage', $context)) {
            throw new \moodle_exception('cannoteditroles');
        }           

        if (!$DB->get_record('role', array('id' => $fromrole))) {
            throw new \moodle_exception('fromrolenotexists');
        }
        if (!$DB->get_record('role', array('id' => $torole))) {
            throw new \moodle_exception('torolenotexists');
        }

        if ($value == 0) {
            if (!$DB->delete_records('role_allow_switch', array('roleid' => $fromrole, 'allowswitch' => $torole))) {
                return false;
            }
        } else if ($value == 1) {
            if (!$DB->get_record('role_allow_switch', array('roleid' => $fromrole, 'allowswitch' => $torole))) {
                //$toinsert = new stdClass();
                $toinsert->roleid = $fromrole;
                $toinsert->allowswitch = $torole;
                if (!$DB->insert_record('role_allow_switch', $toinsert)) {
                    return false;
                }
            }
        } else {
            throw new \moodle_exception('incorrectvalue');
        }
        return true;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function set_role_switch_returns() {
        return new \external_value(PARAM_BOOL, false);
    }
	
	/**
	 * Return the global Role (NEW 10/2020 by p.liersch)	 
     * @param {integer} contextid, {integer} userid
     * @return {array} roleids
     * @throws {moodle_exception}
	 */
	public static function user_get_global_roles($contextid, $userid) {
		global $DB, $USER;
		
        if (!$user = $DB->get_record('user', array('id' => $userid))) {
            throw new \moodle_exception('usernotfound');
        }		

		$roleids = $DB->get_records('role_assignments', ['contextid' => $contextid, 'userid' => $userid]);
		
		return $roleids;
	}
	
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function user_get_global_roles_parameters() {
        return new \external_function_parameters(
                array(
            'contextid' => new \external_value(PARAM_INT, 'Context ID'),
            'userid' => new \external_value(PARAM_INT, 'User ID'),
                )
        );
    }	
	
    /**
     * Returns description of method result value
     * @return user_get_global_roles
     */	
    public static function user_get_global_roles_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(
                array(
                    'roleid' => new \external_value(PARAM_INT, 'some roleid id'),
                )
            )
        );	
    }	
}
