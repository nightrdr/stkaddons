<?php
/**
 * copyright 2011 Stephen Just <stephenjust@users.sf.net>
 *           2013 Glenn De Jonghe
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
require_once(INCLUDE_DIR . 'exceptions.php');

/**
 * Class to contain all string validation functions
 * @author stephenjust
 */
class Validate {
    
    /**
     * Checks a username/email address combination and returns the user id if valid
     * @param string $username
     * @param string $email
     * @throws DBException when something unexpected with the database happened
     * @throws UserException when username/email combination is invalid
     */
    public static function account($username, $email){
        $result = DBConnection::get()->query(
            "SELECT `id`
	        FROM `".DB_PREFIX."users`
	        WHERE `user` = :username
            AND `email` = :email
            AND `active` = 1",
            DBConnection::FETCH_ALL,
            array(
                    ':username'   => $username,
                    ':email'    => $email
            )
        );
        if(count($result) > 1){
            throw new DBException();
        }
        if(count($result) === 0){
            throw new UserException(htmlspecialchars(
                _('Username and email address combination not found.')
            ));
        }
        return $result[0]['id'];
    }
    
    /**
     * Check if the input is a valid email address
     * @param string $email Email address
     * @return string Email address
     */
    public static function email($email) {
        if (strlen($email) == 0) {
            throw new UserException(htmlspecialchars(_('You must enter an email address.')));
        }
        if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',$email)) {
            throw new UserException(htmlspecialchars(sprintf(_('"%s" is not a valid email address.'),$email)));
        }
        return htmlspecialchars($email);
    }
    
    /**
     * Check if the input is a valid alphanumeric username
     * @param string $username Alphanumeric username
     * @return string Username
     */
    public static function username($username) {
        if (strlen($username) < 4) {
            throw new UserException(htmlspecialchars(_('Your username must be at least 4 characters long.')));
        }
        if (!preg_match('/^[a-z0-9]+$/i',$username)) {
            throw new UserException(htmlspecialchars(_('Your username can only contain alphanumeric characters.')));
        }
        return htmlspecialchars($username);
    }
    
    public static function password($password1, $password2 = NULL, $username = NULL, $userid = NULL) {
        // Check password properties
        if (strlen($password1) < 8) {
            throw new UserException(htmlspecialchars(_('Your password must be at least 8 characters long.')));
        }
        if ($password2 != NULL) {
            if ($password1 !== $password2) {
                throw new UserException(htmlspecialchars(_('Your passwords do not match.')));
            }
        }
        // Salt password
        $salt_length = 32;
        if ($username === NULL && $userid === NULL)
            $salt = md5(uniqid(NULL,true));
        else {
            // Get current user password entry to get salt
            
            try{
                if($userid === NULL)
                {
                    $result = DBConnection::get()->query(
                        "SELECT `pass` 
            	        FROM `". DB_PREFIX . "users`
            	        WHERE `user` = :username",
                        DBConnection::FETCH_ALL,
                        array(
                            ':username'   => $username
                        )
                    );
                }else{
                    $result = DBConnection::get()->query(
                        "SELECT `pass`
            	        FROM `". DB_PREFIX . "users`
            	        WHERE `id` = :userid",
                        DBConnection::FETCH_ALL,
                        array(
                            ':userid'   => (int) $userid
                        )
                    );
                }
            }catch(DBException $e){
                throw new UserException(htmlspecialchars(
                    _('An error occurred trying to validate your password.') .' '.
                    _('Please contact a website administrator.')
                ));
            }
            if(count($result) === 0){
                $salt = md5(uniqid(NULL,true));
            }else {
                if (strlen($result[0]['pass']) == 64) {
                    // Not a salted password
                    return hash('sha256',$password1);
                }
                $salt = substr($result[0]['pass'], 0, $salt_length);
            }
        }
        return $salt.hash('sha256',$salt.$password1);
    }
    
    public static function realname($name) {
        if (strlen(trim($name)) < 2) {
            throw new UserException(htmlspecialchars(_('You must enter a name.')));
        }
        return htmlspecialchars(trim($name));
    }
    
    public static function checkbox($box, $message) {
        if ($box !== 'on') {
            throw new UserException($message);
        }
        return $box;
    }
    
    public static function versionString($string) {
	if (!preg_match('/^(svn|[\d]+\.[\d]+\.[\d](-rc[\d])?)$/i', $string)) {
	    throw new Exception('Invalid version string! Format should be: W.X.Y[-rcZ]');
	}
	return true;
    }
    
    /**
     * Check if the input is a valid alphanumeric username
     * @param string $username Alphanumeric username
     * @param string $password unhashed password
     * @return associative array with user information from the database
     */
    public static function credentials($username, $password){
        try{
            $result = DBConnection::get()->query(
                "SELECT `id`,`user`,`pass`,`name`,`role`
                FROM `" . DB_PREFIX . "users`
                WHERE `user` = :username AND `pass` = :pass",
                DBConnection::FETCH_ALL,
                array
                (
                    ':username'   => Validate::username($username),
                    ':pass'   => Validate::password($password, null, $username)
                )
            );
            return $result;
        }
        catch (UserException $e){
            throw new UserException($e->getMessage());
        }
        catch (PDOException $e){
            throw new UserException(htmlspecialchars(
                _('An error occurred while signing in.') .' '.
                _('Please contact a website administrator.')
            ));
        }
    
    }
}
?>
