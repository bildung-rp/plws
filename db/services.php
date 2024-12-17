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
defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_plws_delete_file' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'delete_file',
        'description' => 'Delete File by ID',
        'type' => 'write',
    ),
    'local_plws_user_get_userid_by_username' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'get_userid_by_username',
        'description' => 'Returns user id from username',
        'type' => 'read',
    ),
    'local_plws_role_get_capability' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'role_get_capability',
        'description' => 'For a given role (id) in a context and a specified capability in standard Moodle capability format (e.g. coursereport/log:view) a tuple will be returned as follows: Permission: not set, allow, prevent or prohibit Risk: Array(configuration, XSS, privacy, spam, data loss)',
        'type' => 'read',
    ),
    'local_plws_role_set_capability' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'role_set_capability',
        'description' => 'For a given role (id), a context and a specified capability in Moodle capability format (e.g. coursereport/log:view) the permission will be set, where permission can be not set, allow, prevent or prohibit.',
        'type' => 'write',
    ),
    'local_plws_roles_list' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'roles_list',
        'description' => 'The list of all available roles will be returned in the form of an array, where each tuple contains am id and a role name',
        'type' => 'read',
    ),
    'local_plws_user_assign_authentication' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'user_assign_authentication',
        'description' => 'An authentication method has to be assigned to a given user.',
        'type' => 'write',
    ),
    'local_plws_get_role_assignment' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'get_role_assignment',
        'description' => 'fromrole is allowed (value=1) or not allowed (value=0) to assign torole to users.',
        'type' => 'read',
    ),
    'local_plws_set_role_assignment' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'set_role_assignment',
        'description' => 'If fromrole is allowed to assign torole to users then value=1 else value=0.',
        'type' => 'write',
    ),
    'local_plws_get_role_switch' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'get_role_switch',
        'description' => 'fromrole is allowed (value=1) or not allowed (value=0) to switch to torole.',
        'type' => 'read',
    ),
    'local_plws_set_role_switch' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'set_role_switch',
        'description' => 'If fromrole is allowed to switch to torole then value=1 else value=0.',
        'type' => 'write',
    ),
    'local_plws_user_get_global_roles' => array(
        'classname' => 'local_plws\external',
        'methodname' => 'user_get_global_roles',
        'description' => 'Return roleids from userid and context',
        'type' => 'read',
    ),	
);
