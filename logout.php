<?php
require_once 'lib/common.php';
require_once 'lib/login.php';
session_start();
logout();
redirectAndExit('index.php');

