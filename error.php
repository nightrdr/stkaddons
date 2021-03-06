<?php

/**
 * copyright 2012-2014 Stephen Just <stephenjust@users.sf.net>
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
$error_code = (empty($_GET['e'])) ? NULL : $_GET['e'];

// Send appropriate error header
switch ($error_code) {
    default:
        break;
    case '403':
        header('HTTP/1.1 403 Forbidden');
        break;
    case '404':
        header('HTTP/1.1 404 Not Found');
        break;
}

define('ROOT', './');
require_once(ROOT.'config.php');
require_once(INCLUDE_DIR.'StkTemplate.class.php');

$tpl = new StkTemplate('error-page.tpl');
switch ($error_code) {
    default:
        // I18N: Heading for general error page
        $error_head = htmlspecialchars(_('An Error Occurred'));
        // I18N: Error message for general error page
        $error_text = htmlspecialchars(_('Something broke! We\'ll try to fix it as soon as we can!'));
        break;
    case '403':
        // I18N: Heading for HTTP 403 Forbidden error page
        $error_head = htmlspecialchars(_('403 - Forbidden'));
        // I18N: Error message for HTTP 403 Forbidden error page
        $error_text = htmlspecialchars(_('You\'re not supposed to be here. Click one of the links in the menu above to find some better content.'));
        break;
    case '404':
        // I18N: Heading for HTTP 404 Not Found error page
        $error_head = htmlspecialchars(_('404 - Not Found'));
        // I18N: Error message for HTTP 404 Not Found error page
        $error_text = htmlspecialchars(_('We can\'t find what you are looking for. The link you followed may be broken.'));
        break;
}
$tpl->assign('error', array(
    'title' => $error_head,
    'message' => $error_text
));

echo $tpl;
?>
