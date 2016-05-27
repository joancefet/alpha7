<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan Kröpke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package 2Moons
 * @author Jan Kröpke <info@2moons.cc>
 * @copyright 2012 Jan Kröpke <info@2moons.cc>
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.7.3 (2013-05-19)
 * @info $Id: class.FleetFunctions.php 2677 2013-04-18 10:05:22Z lordmegger@googlemail.com $
 * @link http://2moons.cc/
 */

class FleetFunctions 
{
	static $allowedSpeed	= array(10 => 100, 9 => 90, 8 => 80, 7 => 70, 6 => 60, 5 => 50, 4 => 40, 3 => 30, 2 => 20, 1 => 10);
	
	private static function GetShipConsumption($Ship, $Player)
	{
		global $pricelist;
		$allyInfo = $GLOBALS['DATABASE']->query("SELECT FuelReduce FROM `uni1_alliance` WHERE id = ".$Player['ally_id'].";");
		$allyInfo  = $GLOBALS['DATABASE']->fetch_array($allyInfo);
		return (($Player['impulse_motor_tech'] >= 5 && $Ship == 202) || ($Player['hyperspace_motor_tech'] >= 8 && $Ship == 211)) ? $pricelist[$Ship]['consumption2'] - ($pricelist[$Ship]['consumption2'] / 100 * ($Player['combat_reward_deut'])) - ($pricelist[$Ship]['consumption2'] / 100 * ($allyInfo['FuelReduce'])) - ($pricelist[$Ship]['consumption2'] / 100 * getbonusOneBis(1107,$Player['academy_1107']))  : $pricelist[$Ship]['consumption'] - ($pricelist[$Ship]['consumption'] / 100 * ($Player['combat_reward_deut'])) - ($pricelist[$Ship]['consumption'] / 100 * ($allyInfo['FuelReduce'])) - ($pricelist[$Ship]['consumption'] / 100 * getbonusOneBis(1107,$Player['academy_1107']));
	}

	private static function OnlyShipByID($Ships, $ShipID)
	{
		return isset($Ships[$ShipID]) && count($Ships) === 1;
	}

	private static function GetShipSpeed($Ship, $Player)
	{
		global $pricelist;
		
		$techSpeed	= $pricelist[$Ship]['tech'];
        $baseSpeed = $pricelist[$Ship]['speed'];
		
		if($techSpeed == 4) {
			$techSpeed = $Player['impulse_motor_tech'] >= 5 ? 2 : 1;
            $baseSpeed = $pricelist[$Ship]['speed2'];
		}
		if($techSpeed == 5) {
			$techSpeed = $Player['hyperspace_motor_tech'] >= 8 ? 3 : 2;
            $baseSpeed = $pricelist[$Ship]['speed2'];
		}
			
		$allyInfo = $GLOBALS['DATABASE']->query("SELECT alliance_fleet_speed, SpeedFleet FROM `uni1_alliance` WHERE id = ".$Player['ally_id'].";");
		$allyInfo  = $GLOBALS['DATABASE']->fetch_array($allyInfo);
		
		switch($techSpeed)
		{
			case 1:
				$speed	= $baseSpeed * (1 + (0.1 * $Player['combustion_tech'])) ;
				$speed	= $speed + ($speed / 100 * $allyInfo['alliance_fleet_speed']) ;
				$speed	= $speed + ($speed / 100 * $allyInfo['SpeedFleet']) ;
				$speed	= $speed + ($speed / 100 * getbonusOneBis(1105,$Player['academy_1105'])) ;
			break;
			case 2:
				$speed	= $baseSpeed * (1 + (0.2 * $Player['impulse_motor_tech'])) ;
				$speed	= $speed + ($speed / 100 * $allyInfo['alliance_fleet_speed']) ;
				$speed	= $speed + ($speed / 100 * $allyInfo['SpeedFleet']) ;
				$speed	= $speed + ($speed / 100 * getbonusOneBis(1105,$Player['academy_1105'])) ;
			break;
			case 3:
				$speed	= $baseSpeed * (1 + (0.3 * $Player['hyperspace_motor_tech']));
				$speed	= $speed + ($speed / 100 * $allyInfo['alliance_fleet_speed']) ;
				$speed	= $speed + ($speed / 100 * $allyInfo['SpeedFleet']) ;
				$speed	= $speed + ($speed / 100 * getbonusOneBis(1105,$Player['academy_1105'])) ;
			break;
			default:
				$speed	= 0;
			break;
		}
		
		

		return $speed;
	}
	
	public static function getExpeditionLimit($USER)
	{
		$premium_expedition = 0;
		if($USER['premium_reward_expedition'] > 0 && $USER['premium_reward_expedition_days'] > TIMESTAMP){
		$premium_expedition = $USER['premium_reward_expedition'];
		}
		return floor(sqrt($USER[$GLOBALS['resource'][124]])) + $premium_expedition;
	}
	
	public static function getFortressLimit($USER)
	{
		$max = 3;
		
		return $max;
	}
	
	public static function getDMMissionLimit($USER)
	{
		return Config::get('max_dm_missions');
	}
	
	public static function getMissileRange($Level)
	{
		return max(($Level * 5) - 1, 0);
	}
	
	public static function CheckUserSpeed($speed)
	{
		return isset(self::$allowedSpeed[$speed]);
	}

	public static function GetTargetDistance($start, $target)
	{
		if ($start[0] != $target[0])
			return abs($start[0] - $target[0]) * 20000;
		
		if ($start[1] != $target[1])
			return abs($start[1] - $target[1]) * 95 + 2700;
		
		if ($start[2] != $target[2])
			return abs($start[2] - $target[2]) * 5 + 1000;

		return 5;
	}

	public static function GetMissionDuration($SpeedFactor, $MaxFleetSpeed, $Distance, $GameSpeed, $USER)
	{
		$SpeedFactor	= (3500 / ($SpeedFactor * 0.1));
		$SpeedFactor	*= pow($Distance * 10 / $MaxFleetSpeed, 0.5);
		$SpeedFactor	+= 10;
		$SpeedFactor	/= $GameSpeed;
		
		if(isset($USER['factor']['FlyTime']))
		{
			$SpeedFactor	*= max(0, 1 + $USER['factor']['FlyTime']);
		}
		
		return max($SpeedFactor, MIN_FLEET_TIME);
	}
 
	public static function GetMIPDuration($startSystem, $targetSystem)
	{
		$Distance = abs($startSystem - $targetSystem);
		$Duration = max(round((30 + 60 * $Distance) / self::GetGameSpeedFactor()), MIN_FLEET_TIME);
		
		return $Duration;
	}

	public static function GetGameSpeedFactor()
	{
		return $GLOBALS['CONF']['fleet_speed'] / 2500;
	}
	
	public static function GetMaxFleetSlots($USER)
	{
		global $resource;
		return 1 + $USER[$resource[108]] + $USER['peace_reward_slots'] + getbonusOneBis(1106,$USER['academy_1106']) + $USER['factor']['FleetSlots'];
	}

	public static function GetFleetRoom($Fleet, $Player)
	{
		global $pricelist;
		$FleetRoom 				= 0;
		$allyInfo = $GLOBALS['DATABASE']->query("SELECT FleetCapa FROM `uni1_alliance` WHERE id = ".$Player['ally_id'].";");
		$allyInfo  = $GLOBALS['DATABASE']->fetch_array($allyInfo);
		foreach ($Fleet as $ShipID => $amount)
		{
			$FleetRoom		   += $pricelist[$ShipID]['capacity'] * $amount;
			$FleetRoom		   += ($FleetRoom / 100 * getbonusOneBis(1207,$Player['academy_1207'])) + ($FleetRoom / 100 * $allyInfo['FleetCapa']);
			
			}
		return $FleetRoom; 
	}
	
	public static function GetFleetMaxSpeed ($Fleets, $Player)
	{
		$FleetArray = (!is_array($Fleets)) ? array($Fleets => 1) : $Fleets;
		$speedalls 	= array();
		
		foreach ($FleetArray as $Ship => $Count) {
			$speedalls[$Ship] = self::GetShipSpeed($Ship, $Player);
		}
		
		return min($speedalls);
	}

	public static function GetFleetConsumption($FleetArray, $MissionDuration, $MissionDistance, $FleetMaxSpeed, $Player, $GameSpeed)
	{
		$consumption = 0;
		$premium_comsumption = 0;
		if($Player['prem_fuel_consumption'] > 0 && $Player['prem_fuel_consumption_days'] > TIMESTAMP){
		$premium_comsumption = $Player['prem_fuel_consumption'];
		}
		foreach ($FleetArray as $Ship => $Count)
		{
			$ShipSpeed          = self::GetShipSpeed($Ship, $Player);
			$ShipConsumption    = self::GetShipConsumption($Ship, $Player);
			
			$spd                = 35000 / (round($MissionDuration, 0) * $GameSpeed - 10) * sqrt($MissionDistance * 10 / $ShipSpeed);
			$basicConsumption   = $ShipConsumption * $Count;
			$consumption        += $basicConsumption * $MissionDistance / 35000 * (($spd / 10) + 1) * (($spd / 10) + 1);
			$consumption        = $consumption - ($consumption / 100 * $premium_comsumption);
		}
		return (round($consumption) + 1);
	}

	public static function GetFleetMissions($USER, $MisInfo, $Planet)
	{
		global $resource;
		$Missions	= self::GetAvailableMissions($USER, $MisInfo, $Planet);
		$stayBlock	= array();;
		if (in_array(15, $Missions)) {
			$stayBlock = array(1 => 0.14, 2 => 0.29, 3 => 0.43, 4 => 0.57);
		}
		elseif(in_array(11, $Missions)) 
		{
			$stayBlock = array(1 => 1);
		}
		elseif(in_array(5, $Missions)) 
		{
			$stayBlock = array(1 => 1, 2 => 2, 4 => 4, 8 => 8, 12 => 12, 16 => 16, 32 => 32);
		}
		
		return array('MissionSelector' => $Missions, 'StayBlock' => $stayBlock);
	}
	
	public static function GetACSDuration($FleetGroup)
	{
				if(empty($FleetGroup))
			return 0;
			
		$GetAKS 	= $GLOBALS['DATABASE']->getFirstCell("SELECT ankunft FROM ".AKS." WHERE id = ".$FleetGroup.";");

		return !empty($GetAKS) ? $GetAKS - TIMESTAMP : 0;
	}
	
	public static function setACSTime($timeDifference, $FleetGroup)
	{
		
		if(empty($FleetGroup))
			return false;
			
		$GLOBALS['DATABASE']->multi_query("UPDATE ".AKS." SET ankunft = ankunft + ".$timeDifference." WHERE id = ".$FleetGroup.";
						  UPDATE ".FLEETS.", ".FLEETS_EVENT." SET 
						  fleet_start_time = fleet_start_time + ".$timeDifference.",
						  fleet_end_stay   = fleet_end_stay + ".$timeDifference.",
						  fleet_end_time   = fleet_end_time + ".$timeDifference.",
						  time             = time + ".$timeDifference."
						  WHERE fleet_group = ".$FleetGroup." AND fleet_id = fleetID;");

        return true;
	}

	public static function GetCurrentFleets($USERID, $Mission = 0)
	{
		
		$ActualFleets = $GLOBALS['DATABASE']->getFirstRow("SELECT COUNT(*) as state FROM ".FLEETS." WHERE fleet_owner = '".$USERID."' AND ".(($Mission != 0)?"fleet_mission = '".$Mission."'":"fleet_mission != 10").";");
		return $ActualFleets['state'];
	}	
	
	public static function SendFleetBack($USER, $FleetID)
	{
		$tickatual = buscarTick();

		$FleetRow = $GLOBALS['DATABASE']->getFirstRow("SELECT start_time, fleet_mission, fleet_group, fleet_owner, fleet_mess, tickinicial, tickfinal, tickretorno FROM ".FLEETS." WHERE fleet_id = '". $FleetID ."';");
		if ($FleetRow['fleet_owner'] != $USER['id'] || $FleetRow['fleet_mess'] == 1)
			return;
			
		$sqlWhere	= 'fleet_id';

		if($FleetRow['fleet_mission'] == 1 && $FleetRow['fleet_group'] != 0)
		{
			$acsResult = $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".USERS_ACS." WHERE acsID = ".$FleetRow['fleet_group'].";");

			if($acsResult != 0)
			{
				$GLOBALS['DATABASE']->multi_query("DELETE FROM ".AKS." WHERE id = ".$FleetRow['fleet_group'].";
								  DELETE FROM ".USERS_ACS." WHERE acsID = ".$FleetRow['fleet_group'].";");
				
				$FleetID	= $FleetRow['fleet_group'];
				$sqlWhere	= 'fleet_group';
			}
		}
		$tickretorno = $tickatual - $FleetRow['tickinicial'] + $tickatual;
		$tickfinal = $tickatual;
		
		$fleetEndTime	= (TIMESTAMP - $FleetRow['start_time']) + TIMESTAMP;
		
		$GLOBALS['DATABASE']->multi_query("UPDATE ".FLEETS.", ".FLEETS_EVENT." SET 
						  fleet_group = 0,
						  fleet_end_stay = ".TIMESTAMP.",
						  fleet_end_time = ".$fleetEndTime.",
						  tickfinal = ".$tickfinal.",
						  tickretorno = ".$tickretorno.",
						  fleet_mess = 1,
						  hasCanceled = 1,
						  time = ".$fleetEndTime."
						  WHERE ".$sqlWhere." = ".$FleetID." AND fleet_id = fleetID;
						  UPDATE ".LOG_FLEETS." SET
						  fleet_end_stay = ".TIMESTAMP.",
						  fleet_end_time = ".$fleetEndTime.",
						  tickfinal = ".$tickfinal.",
						  tickretorno = ".$tickretorno.",
						  fleet_mess = 1,
						  fleet_state = 2
						  WHERE ".$sqlWhere." = ".$FleetID.";");
	}
	
	public static function GetFleetShipInfo($FleetArray, $Player)
	{
		$FleetInfo	= array();
		foreach ($FleetArray as $ShipID => $Amount) {
			$FleetInfo[$ShipID]	= array('consumption' => self::GetShipConsumption($ShipID, $Player), 'speed' => self::GetFleetMaxSpeed($ShipID, $Player), 'amount' => floattostring($Amount));
		}
		return $FleetInfo;
	}
	
	public static function GotoFleetPage($Code = 0)
	{	
		global $LNG;
		$temp = debug_backtrace();
		if($GLOBALS['CONF']['debug'] == 1)
		{
			exit(str_replace($_SERVER["DOCUMENT_ROOT"],'.',$temp[0]['file'])." on ".$temp[0]['line']. " | Code: ".$Code." | Error: ".(isset($LNG['fl_send_error'][$Code]) ? $LNG['fl_send_error'][$Code] : ''));
		}
		
		HTTP::redirectTo('game.php?page=fleetTable&code='.$Code);
	}
	
	public static function GetAvailableMissions($USER, $MissionInfo, $GetInfoPlanet)
	{	
		global $PLANET;
		$YourPlanet				= (!empty($GetInfoPlanet['id_owner']) && $GetInfoPlanet['id_owner'] == $USER['id']) ? true : false;
		$UsedPlanet				= (!empty($GetInfoPlanet['id_owner'])) ? true : false;
		$avalibleMissions		= array();
		
		if ($MissionInfo['planet'] == (Config::get('max_planets') + 1) && $USER['immunity_until'] < TIMESTAMP)
			$avalibleMissions[]	= 15;	
		elseif ($MissionInfo['planettype'] == 2) {
			if ((isset($MissionInfo['Ship'][209]) || isset($MissionInfo['Ship'][219])) && $GetInfoPlanet['id_owner'] != Asteroid_Id && $GetInfoPlanet['planet_type'] != 4 && isModulAvalible(MODULE_MISSION_RECYCLE) && !($GetInfoPlanet['der_metal'] == 0 && $GetInfoPlanet['der_crystal'] == 0))
				$avalibleMissions[]	= 8;
		} else {
			if (!$UsedPlanet) {
				if (isset($MissionInfo['Ship'][208]) && $GetInfoPlanet['id_owner'] != Asteroid_Id && $MissionInfo['planettype'] == 1 && isModulAvalible(MODULE_MISSION_COLONY))
					$avalibleMissions[]	= 7;
			} else {
				if(isModulAvalible(MODULE_MISSION_TRANSPORT) && $GetInfoPlanet['id_owner'] != Asteroid_Id && $GetInfoPlanet['planet_type'] != 4)
					$avalibleMissions[]	= 3;
				
				if(isModulAvalible(MODULE_MISSION_TRANSPORT) && $GetInfoPlanet['id_owner'] == $USER['id'] && $GetInfoPlanet['planet_type'] == 4)
					$avalibleMissions[]	= 3;

				if (!$YourPlanet && $USER['immunity_until'] < TIMESTAMP && $GetInfoPlanet['id_owner'] != Asteroid_Id && self::OnlyShipByID($MissionInfo['Ship'], 210) && isModulAvalible(MODULE_MISSION_SPY))
					$avalibleMissions[]	= 6;
				
				if($GetInfoPlanet['id_owner'] == Asteroid_Id && $GetInfoPlanet['planet_type'] != 4 && $MissionInfo['planettype'] == 1)
				$avalibleMissions[]	= 12;	
			
			
				if($GetInfoPlanet['id_owner'] == Fortress_Id && $GetInfoPlanet['planet_type'] == 4 && $MissionInfo['planettype'] == 4)
				$avalibleMissions[]	= 20;	 

				if (!$YourPlanet) {
					if(isModulAvalible(MODULE_MISSION_ATTACK) && $USER['immunity_until'] < TIMESTAMP && $PLANET['last_relocate'] < TIMESTAMP - 15 * 60 && $GetInfoPlanet['id_owner'] != Asteroid_Id && $GetInfoPlanet['planet_protection'] < TIMESTAMP && $GetInfoPlanet['planet_type'] != 4)
						$avalibleMissions[]	= 1;
				
					if(isModulAvalible(MODULE_MISSION_HOLD) && $USER['immunity_until'] < TIMESTAMP && $GetInfoPlanet['id_owner'] != Asteroid_Id && $GetInfoPlanet['planet_type'] != 4)
						$avalibleMissions[]	= 5;}
						
				elseif(isModulAvalible(MODULE_MISSION_STATION) && $GetInfoPlanet['id_owner'] != Asteroid_Id && $GetInfoPlanet['planet_type'] != 4) {
					$avalibleMissions[]	= 4;}
					
				if (!empty($MissionInfo['IsAKS']) && $USER['immunity_until'] < TIMESTAMP && $GetInfoPlanet['id_owner'] != Asteroid_Id && $GetInfoPlanet['planet_type'] != 4 && !$YourPlanet && isModulAvalible(MODULE_MISSION_ATTACK) && isModulAvalible(MODULE_MISSION_ACS))
					$avalibleMissions[]	= 2;

				if (!$YourPlanet && $USER['immunity_until'] < TIMESTAMP && $GetInfoPlanet['id_owner'] != Asteroid_Id && $GetInfoPlanet['planet_type'] != 4 && $MissionInfo['planettype'] == 3 && $PLANET['last_relocate'] < TIMESTAMP - 15 * 60 && isset($MissionInfo['Ship'][214]) && isModulAvalible(MODULE_MISSION_DESTROY) && $USER['rpg_destructeur'] > 0)
					$avalibleMissions[]	= 9;

				if ($YourPlanet && $GetInfoPlanet['id_owner'] != Asteroid_Id && $GetInfoPlanet['planet_type'] != 4 && $MissionInfo['planettype'] == 3 && self::OnlyShipByID($MissionInfo['Ship'], 220) && isModulAvalible(MODULE_MISSION_DARKMATTER))
					$avalibleMissions[]	= 11;
			}
		}
		
		return $avalibleMissions;
	}
	
	public static function CheckBash($Target)
	{
		global $USER;
		if(!BASH_ON)
			return false;
			
		$Count	= $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".LOG_FLEETS."
		WHERE fleet_owner = ".$USER['id']." 
		AND fleet_end_id = ".$Target." 
		AND fleet_state != 2 
		AND fleet_start_time > ".(TIMESTAMP - BASH_TIME)." 
		AND fleet_mission IN (1,2,9);");
		return $Count >= BASH_COUNT;
	}
	
	public static function sendFleet($fleetArray, $fleetMission, $fleetStartOwner, $fleetStartPlanetID, $fleetStartPlanetGalaxy, $fleetStartPlanetSystem, $fleetStartPlanetPlanet, $fleetStartPlanetType, $fleetTargetOwner, $fleetTargetPlanetID, $fleetTargetPlanetGalaxy, $fleetTargetPlanetSystem, $fleetTargetPlanetPlanet, $fleetTargetPlanetType, $fleetRessource, $fleetStartTime, $fleetStayTime, $fleetEndTime, $tickinicial, $tickfinal, $fleetGroup = 0, $missleTarget = 0)
	{
		global $resource, $UNI;
		$fleetShipCount	= array_sum($fleetArray);
		$fleetData		= array();
		$planetQuery	= "";
		foreach($fleetArray as $ShipID => $ShipCount) {
			$fleetData[]	= $ShipID.','.floattostring($ShipCount);
			$planetQuery[]	= $resource[$ShipID]." = ".$resource[$ShipID]." - ".floattostring($ShipCount);
		}
		$tickretorno = $tickfinal - $tickinicial + $tickfinal;
		
		$SQL	= "LOCK TABLE uni1_fleets_alarm WRITE, ".LOG_FLEETS." WRITE, ".FLEETS_EVENT." WRITE, ".FLEETS." WRITE, ".PLANETS." WRITE;
				   UPDATE ".PLANETS." SET ".implode(", ", $planetQuery)." WHERE id = ".$fleetStartPlanetID.";
				   INSERT INTO ".FLEETS." SET
				   fleet_owner              = ".$fleetStartOwner.",
				   fleet_target_owner       = ".$fleetTargetOwner.",
				   fleet_mission            = ".$fleetMission.",
				   fleet_amount             = ".$fleetShipCount.",
				   fleet_array              = '".implode(';',$fleetData)."',
				   fleet_universe	        = ".$UNI.",
				   fleet_start_time         = ".$fleetStartTime.",
				   fleet_end_stay           = ".$fleetStayTime.",
				   fleet_end_time           = ".$fleetEndTime.",
				   fleet_start_id           = ".$fleetStartPlanetID.",
				   fleet_start_galaxy       = ".$fleetStartPlanetGalaxy.",
				   fleet_start_system       = ".$fleetStartPlanetSystem.",
				   fleet_start_planet       = ".$fleetStartPlanetPlanet.",
				   fleet_start_type         = ".$fleetStartPlanetType.",
				   fleet_end_id             = ".$fleetTargetPlanetID.",
				   fleet_end_galaxy         = ".$fleetTargetPlanetGalaxy.",
				   fleet_end_system         = ".$fleetTargetPlanetSystem.",
				   fleet_end_planet         = ".$fleetTargetPlanetPlanet.",
				   fleet_end_type           = ".$fleetTargetPlanetType.",
				   fleet_resource_metal     = ".$fleetRessource[901].",
				   fleet_resource_crystal   = ".$fleetRessource[902].",
				   fleet_resource_deuterium = ".$fleetRessource[903].",
				   fleet_group              = ".$fleetGroup.",
				   fleet_target_obj         = ".$missleTarget.",
				   tickinicial				= ".$tickinicial.",
				   tickfinal				= ".$tickfinal.",
				   tickretorno				= ".$tickretorno.",
				   start_time               = ".TIMESTAMP.";
				   SET @fleetID = LAST_INSERT_ID();
				   INSERT INTO uni1_fleets_alarm SET
				   fleet_owner              = ".$fleetStartOwner.",
				   fleet_target_owner       = ".$fleetTargetOwner.",
				   fleet_mission            = ".$fleetMission.",
				   fleet_amount             = ".$fleetShipCount.",
				   fleet_array              = '".implode(';',$fleetData)."',
				   fleet_universe	        = ".$UNI.",
				   fleet_start_time         = ".$fleetStartTime.",
				   fleet_end_stay           = ".$fleetStayTime.",
				   fleet_end_time           = ".$fleetEndTime.",
				   fleet_start_id           = ".$fleetStartPlanetID.",
				   fleet_start_galaxy       = ".$fleetStartPlanetGalaxy.",
				   fleet_start_system       = ".$fleetStartPlanetSystem.",
				   fleet_start_planet       = ".$fleetStartPlanetPlanet.",
				   fleet_start_type         = ".$fleetStartPlanetType.",
				   fleet_end_id             = ".$fleetTargetPlanetID.",
				   fleet_end_galaxy         = ".$fleetTargetPlanetGalaxy.",
				   fleet_end_system         = ".$fleetTargetPlanetSystem.",
				   fleet_end_planet         = ".$fleetTargetPlanetPlanet.",
				   fleet_end_type           = ".$fleetTargetPlanetType.",
				   fleet_resource_metal     = ".$fleetRessource[901].",
				   fleet_resource_crystal   = ".$fleetRessource[902].",
				   fleet_resource_deuterium = ".$fleetRessource[903].",
				   fleet_group              = ".$fleetGroup.",
				   fleet_target_obj         = ".$missleTarget.",
				   called         			= '0',
				   tickinicial				= ".$tickinicial.",
				   tickfinal				= ".$tickfinal.",
				   tickretorno				= ".$tickretorno.",
				   start_time               = ".TIMESTAMP.";

				   
                   INSERT INTO ".FLEETS_EVENT." SET 
				   fleetID                  = @fleetID,
				   `time`                   = ".$fleetStartTime.";
				   INSERT INTO ".LOG_FLEETS." SET 
				   fleet_id                 = @fleetID, 
				   fleet_owner              = ".$fleetStartOwner.",
				   fleet_target_owner       = ".$fleetTargetOwner.",
				   fleet_mission            = ".$fleetMission.",
				   fleet_amount             = ".$fleetShipCount.",
				   fleet_array              = '".implode(',',$fleetData)."',
				   fleet_universe	        = ".$UNI.",
				   fleet_start_time         = ".$fleetStartTime.",
				   fleet_end_stay           = ".$fleetStayTime.",
				   fleet_end_time           = ".$fleetEndTime.",
				   fleet_start_id           = ".$fleetStartPlanetID.",
				   fleet_start_galaxy       = ".$fleetStartPlanetGalaxy.",
				   fleet_start_system       = ".$fleetStartPlanetSystem.",
				   fleet_start_planet       = ".$fleetStartPlanetPlanet.",
				   fleet_start_type         = ".$fleetStartPlanetType.",
				   fleet_end_id             = ".$fleetTargetPlanetID.",
				   fleet_end_galaxy         = ".$fleetTargetPlanetGalaxy.",
				   fleet_end_system         = ".$fleetTargetPlanetSystem.",
				   fleet_end_planet         = ".$fleetTargetPlanetPlanet.",
				   fleet_end_type           = ".$fleetTargetPlanetType.",
				   fleet_resource_metal     = ".$fleetRessource[901].",
				   fleet_resource_crystal   = ".$fleetRessource[902].",
				   fleet_resource_deuterium = ".$fleetRessource[903].",
				   fleet_group              = ".$fleetGroup.",
				   fleet_target_obj         = ".$missleTarget.",
				   tickinicial				= ".$tickinicial.",
				   tickfinal				= ".$tickfinal.",
				   tickretorno				= ".$tickretorno.",
				   start_time               = ".TIMESTAMP.";
				   UNLOCK TABLES;";
				   
		$GLOBALS['DATABASE']->multi_query($SQL);
	}
}