<?php

namespace Nottingham\ValidationTweaker;

ignore_user_abort(true);
\System::increaseMaxExecTime(1800);


$projectID = $module->getProjectId();

// Check all the necessary parameters are supplied, and that either the server or the project is
// development. Stop here if these are not satisfied.
if ( $projectID == null || ! is_string( $_GET['record'] ) ||
     ! preg_match( '/^[1-9][0-9]*$/', $_GET['interval'] ) ||
     ! preg_match( '/^[1-9][0-9]*$/', $_GET['iterations'] ) ||
     ! preg_match( '/^[1-9][0-9]*$/', $_GET['event_id'] ) ||
     ! preg_match( '/^[1-9][0-9]*$/', $_GET['instance'] ) ||
     ( $module->getProjectStatus() != 'DEV' &&
       $module->query( 'SELECT `value` v FROM redcap_config WHERE field_name = ?',
                       ['is_development_server'] )->fetch_assoc()['v'] != '1' ) )
{
	exit;
}

// Check that a baseline date field is defined in the module settings. Stop here if not.
$dateField = $module->getProjectSetting( 'baseline-date' );
if ( $dateField == '' )
{
	exit;
}

// Get the parameter values and the current value of the baseline date field.
$record = $_GET['record'];
$eventID = intval( $_GET['event_id'] );
$instance = intval( $_GET['instance'] );
$interval = intval( $_GET['interval'] );
$iterations = intval( $_GET['iterations'] );
$iterations = $iterations < 1 ? 1 : $iterations;
$iterations = $iterations > 150 ? 150 : $iterations;
$slow = isset( $_GET['slow'] );

$listData = \REDCap::getData( 'json-array', $record,
                              [ \REDCap::getRecordIdField(), $dateField ], $eventID );

// Prepare the input data for saving.
$input = [ \REDCap::getRecordIdField() => $record ];
foreach ( $listData as $itemData )
{
	if ( ( count( $listData ) == 1 || $itemData['redcap_repeat_instance'] == $instance ) &&
	     ( $itemData[ $dateField ] ?? '' ) != '' )
	{
		foreach ( [ 'redcap_event_name', 'redcap_repeat_instrument',
		            'redcap_repeat_instance', $dateField ] as $field )
		{
			if ( isset( $itemData[ $field ] ) )
			{
				$input[ $field ] = $itemData[ $field ];
			}
		}
		break;
	}
}

// Perform a save operation every 10 seconds (60 seconds if slow iterations selected), subtracting
// the interval from the date each time.
for ( $i = 0; $i < $iterations; $i++ )
{
	if ( $i > 0 )
	{
		sleep( $slow ? 60 : 10 );
	}
	$input[ $dateField ] = date( 'Y-m-d', mktime( 0, 0, 0, substr( $input[ $dateField ], 5, 2 ),
	                                              substr( $input[ $dateField ], 8, 2 ) - $interval,
	                                              substr( $input[ $dateField ], 0, 4 ) ) ) .
	                       substr( $input[ $dateField ], 10 );
	\REDCap::saveData( 'json-array', [ $input ] );

	// If the esendex alerts module is enabled, trigger its alerts to be evaluated and sent
	// immediately on each iteration.
	if ( $module->isModuleEnabled( 'esendex_alerts', $projectID ) )
	{
		$esendex = \ExternalModules\ExternalModules::getModuleInstance('esendex_alerts');
		$esendex->checkProjectAlertsWithDatediff( $projectID );
		$esendex->sendProjectNotifications( $projectID );
	}
}