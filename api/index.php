<?php

declare(strict_types=1);

if (getenv('VERCEL')) {
	$_SERVER['SCRIPT_FILENAME'] ??= __FILE__;
	$_SERVER['SCRIPT_NAME'] ??= '/';
	$_SERVER['PHP_SELF'] ??= '/';
	$_SERVER['REQUEST_URI'] ??= '/';
	$_SERVER['DOCUMENT_ROOT'] ??= dirname(__DIR__) . '/web';
}

require dirname(__DIR__) . '/web/index.php';