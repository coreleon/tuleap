<?php
/**
 * Copyright (c) Enalean, 2012. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once 'pre.php';
require_once 'common/project/Project_SOAPServer.class.php';
require_once 'common/soap/SOAP_RequestLimitatorFactory.class.php';
require_once 'common/user/GenericUserFactory.class.php';
require_once 'common/project/CustomDescription/CustomDescriptionFactory.class.php';
require_once 'common/project/CustomDescription/CustomDescriptionValueManager.class.php';
require_once 'common/project/CustomDescription/CustomDescriptionDao.class.php';
require_once 'common/project/CustomDescription/CustomDescriptionValueDao.class.php';
require_once 'common/project/CustomDescription/CustomDescriptionValueFactory.class.php';
require_once 'common/project/Service/ServiceUsageFactory.class.php';
require_once 'common/project/Service/ServiceUsageManager.class.php';

// Check if we the server is in secure mode or not.
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || $GLOBALS['sys_force_ssl'] == 1) {
    $protocol = "https";
} else {
    $protocol = "http";
}
$uri = $protocol.'://'.$GLOBALS['sys_default_domain'].'/soap/project';

$serviceClass = 'Project_SOAPServer';

if ($request->exist('wsdl')) {
    require_once 'common/soap/SOAP_NusoapWSDL.class.php';
    $wsdlGen = new SOAP_NusoapWSDL($serviceClass, 'TuleapProjectAPI', $uri);
    $wsdlGen->dumpWSDL();
} else {
    $userManager      = UserManager::instance();
    $projectManager   = ProjectManager::instance();
    $soapLimitFactory = new SOAP_RequestLimitatorFactory();
    
    $ruleShortName        = new Rule_ProjectName();
    $ruleFullName         = new Rule_ProjectFullName();
    $projectCreator       = new ProjectCreator($projectManager, $ruleShortName, $ruleFullName);
    $generic_user_dao     = new GenericUserDao();
    $generic_user_factory = new GenericUserFactory($userManager, $projectManager, $generic_user_dao);
    $limitator            = $soapLimitFactory->getLimitator();

    $custom_project_description_dao       = new Project_CustomDescription_CustomDescriptionDao();
    $custom_project_description_value_dao = new Project_CustomDescription_CustomDescriptionValueDao();

    $custom_project_description_factory = new Project_CustomDescription_CustomDescriptionFactory($custom_project_description_dao);
    $custom_project_description_manager = new Project_CustomDescription_CustomDescriptionValueManager($custom_project_description_value_dao);

    $custom_project_description_value_factory = new Project_CustomDescription_CustomDescriptionValueFactory($custom_project_description_value_dao);

    $service_usage_dao     = new Project_Service_ServiceUsageDao();
    $service_usage_factory = new Project_Service_ServiceUsageFactory($service_usage_dao);
    $service_usage_manager = new Project_Service_ServiceUsageManager($service_usage_dao);

    $server = new SoapServer($uri.'/?wsdl',
                             array('cache_wsdl' => WSDL_CACHE_NONE));
    $server->setClass(
        $serviceClass,
        $projectManager,
        $projectCreator,
        $userManager,
        $generic_user_factory,
        $limitator,
        $custom_project_description_factory,
        $custom_project_description_manager,
        $custom_project_description_value_factory,
        $service_usage_factory,
        $service_usage_manager
    );
    $server->handle();
}

?>
