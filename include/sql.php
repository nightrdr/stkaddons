<?php
/*
copyright 2010 Lucas Baudin <xapantu@gmail.com>                   
                                                                          
This file is part of stkaddons

stkaddons is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

stkaddons is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of       
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with stkaddons.  If not, see <http://www.gnu.org/licenses/>.
*/

mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db(DB_NAME) or die(mysql_error());
mysql_set_charset('utf8');

function sql_query($query)
{
    $error = true;
    $sql = mysql_query($query) or $error = false;
    if (!$error)
    {
	return false;
    }
    else
    {
	return $sql;
    }
}

function sql_next($sql_query)
{
    $exist = true;
    $array = mysql_fetch_array($sql_query) or $exist = false;
    if($exist)
    {
        return $array;
    }
    else
    {
        return false;
    }
}