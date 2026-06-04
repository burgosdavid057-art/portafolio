<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
admin_session_start();
if (!admin_is_setup())    redirect('/admin/setup.php');
if (!admin_is_authed())   redirect('/admin/login.php');
redirect('/admin/projects.php');
