<?php
/**
 * Doctor Accept/Decline endpoint — disabled.
 * Doctors may only view appointments; Root Admin manages booking status.
 */
include(dirname(dirname(dirname(__FILE__))) . "/header.php");

if (!isset($_SESSION)) {
	session_start();
}

header('Content-Type: text/plain; charset=utf-8');
http_response_code(403);
echo 'Forbidden: Doctor cannot Accept/Decline appointments.';
exit;
