<?php
 /**
 * @file 		goGetAllMusicOnHold.php
 * @brief 		API for Getting All Music On Hold
 * @copyright   Copyright (c) 2018 GOautodial Inc.
 * @author      Demian Lizandro A. Biscocho
 * @author		Jeremiah Sebastian Samatra
 * @author     	Chris Lomuntad
 *
 * @par <b>License</b>:
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

	include_once ("goAPI.php");
	
	$limit 												= (isset($_REQUEST['limit'])) ? $astDB->escape($_REQUEST['limit']) : 1000;

	// Error Checking
	if (empty($goUser) || is_null($goUser)) {
		$apiresults 									= array(
			"result" 										=> "Error: goAPI User Not Defined."
		);
	} elseif (empty($goPass) || is_null($goPass)) {
		$apiresults 									= array(
			"result" 										=> "Error: goAPI Password Not Defined."
		);
	} elseif (empty($log_user) || is_null($log_user)) {
		$apiresults 									= array(
			"result" 										=> "Error: Session User Not Defined."
		);
	} else {		
		// check if goUser and goPass are valid
		$fresults										= $astDB
			->where("user", $goUser)
			->where("pass_hash", $goPass)
			->getOne("vicidial_users", "user,user_level");
		
		$goapiaccess									= $astDB->getRowCount();
		$userlevel										= $fresults["user_level"];
		
		if ($goapiaccess > 0 && $userlevel > 7) {	
			// set tenant value to 1 if tenant - saves on calling the checkIfTenantf function
			// every time we need to filter out requests
			$tenant										=  (checkIfTenant ($log_group, $goDB)) ? 1 : 0;
			
			if ($tenant) {
				$astDB->where("user_group", $log_group);
				$astDB->orWhere("user_group", "---ALL---");
			} else {
				if (strtoupper($log_group) !== 'ADMIN') {
					//if ($userlevel > 8) {
						$astDB->where("user_group", $log_group);
						$astDB->orWhere("user_group", "---ALL---");
					//}
				}					
			}
			
			$cols 										= array("moh_id", "moh_name", "active", "random", "user_group");
			$astDB->where("remove", "N", "=");
			$rsltv 										= $astDB->get("vicidial_music_on_hold", $limit, $cols);

			foreach ($rsltv as $fresults) {
				$dataModId[] 							= $fresults['moh_id'];
				$dataMohName[] 							= $fresults['moh_name'];
				$dataActive[] 							= $fresults['active'];
				$dataRandom[] 							= $fresults['random'];
				$dataUserGroup[] 						= $fresults['user_group'];
				
				$apiresults 							= array(
					"result" 								=> "success", 
					"moh_id" 								=> $dataModId, 
					"moh_name" 								=> $dataMohName, 
					"active" 								=> $dataActive, 
					"random" 								=> $dataRandom, 
					"user_group" 							=> $dataUserGroup
				);
			}
		} else {
			$err_msg 									= error_handle("10001");
			$apiresults 								= array(
				"code" 										=> "10001", 
				"result" 									=> $err_msg
			);		
		}
	}			
?>
