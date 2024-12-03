<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: antony Groupware GmbH <info@die-groupware.de>
// SPDX-License-Identifier: AGPL-3.0-or-later


$requirements = [
	'apiVersion' => 'v1'
];


/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\AntonyApi\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */

return [
  	'ocs' => [
  		[
			'name' => 'AntonyAPI#renameShareFolder', 
			'url' => '/', 
			'verb' => 'PUT', 
			'requirements' => $requirements
		],
		[
		  'name' => 'AntonyAPI#version', 
		  'url' => '/version', 
		  'verb' => 'GET', 
		  'requirements' => $requirements
	  ],
  		[
			'name' => 'AntonyAPI#checkDeleteProjectFolder', 
			'url' => '/', 
			'verb' => 'DELETE', 
			'requirements' => $requirements
		],
  	],
];
