<?php
/**
 * Copyright 2011-2013 Stephen Just <stephenjust@users.sourceforge.net>
 *
 * This file is part of stkaddons
 *
 * stkaddons is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * stkaddons is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with stkaddons.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(INCLUDE_DIR . 'DBConnection.class.php');

/**
 * Contains static methods to manage configuration values in database
 */
class ConfigManager
{
    private static $cache = array();

    public static function get_config($config_name) {
        // Validate parameters
        if (!is_string($config_name)) return NULL;
        if (empty($config_name)) return NULL;

        $db = DBConnection::get();

        // Populate the config cache
        if (count(ConfigManager::$cache == 0)) {
            try {
                $result = $db->query('SELECT `name`, `value`' .
                                     'FROM `'.DB_PREFIX.'config`',
                                     DBConnection::FETCH_ALL);
            } catch (DBException $e) {
                if (DEBUG_MODE) echo $e->getMessage();
                return NULL;
            }
            if (count($result) === 0) return null;
            
            foreach ($result AS $row) {
                ConfigManager::$cache[$row['name']] = $row['value'];
            }
        }
        if (!isset(ConfigManager::$cache[$config_name])) {
            return NULL;
        }
        return ConfigManager::$cache[$config_name];
    }

    public static function set_config($config_name,$config_value) {
            // Validate parameters
            if (!is_string($config_name)) return false;
            if (strlen($config_name) < 1) return false;
            if (is_array($config_value)) return false;
            if (empty($config_value)) return true;  // Not changed because we
                                                    // can't accept null values
            $db = DBConnection::get();
            try {
                $db->query(
                        'INSERT INTO `'.DB_PREFIX.'config` '.
                        '    (`name`, `value`) '.
                        'VALUES '.
                        '    (:name, :value) '.
                        'ON DUPLICATE KEY UPDATE `value` = :value',
                        DBConnection::NOTHING,
                        array(
                            ':name' =>  (string) $config_name,
                            ':value' => (string) $config_value
                        ));
            } catch (DBException $e) {
                if (DEBUG_MODE) echo $e->getMessage();
                return false;
            }

            // Update cache - first, make sure the cache exists
            ConfigManager::get_config($config_name);
            ConfigManager::$cache[$config_name] = $config_value;

            return true;
    }
}
?>
