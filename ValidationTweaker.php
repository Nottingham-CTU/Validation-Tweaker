<?php

namespace Nottingham\ValidationTweaker;

class ValidationTweaker extends \ExternalModules\AbstractExternalModule
{
	const VTYPE = 'text_validation_type_or_show_slider_number';
	const VMIN = 'text_validation_min';



	// Amend the settings for REDCap v12.1.0 and greater.

	public function redcap_module_configuration_settings( $project_id, $settings )
	{
		if ( $project_id !== null && \REDCap::versionCompare( REDCAP_VERSION, '12.1.0' ) >= 0 &&
		     ! $this->getProjectSetting( 'no-past-dates', $project_id ) &&
		     ! $this->framework->getUser()->isSuperUser() )
		{
			foreach ( $settings as $k => $v )
			{
				if ( $v['key'] == 'no-past-dates' )
				{
					$settings[$k]['branchingLogic'] =
						[ 'field' => 'no-past-dates', 'value' => true ];
					break;
				}
			}
		}
		return $settings;
	}



	// If the skip validation of required fields option is enabled for surveys, temporarily deem all
	// required fields to be not required when a survey is submitted using this option.

	public function redcap_every_page_before_render()
	{
		if ( $this->getProjectSetting( 'survey-skip-validate' ) &&
		     $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_GET['__skipvalidate'] ) &&
		     substr( PAGE_FULL, 0, strlen( APP_PATH_SURVEY ) ) == APP_PATH_SURVEY )
		{
			foreach ($GLOBALS['Proj']->metadata as $fieldName => $fieldData)
			{
				$GLOBALS['Proj']->metadata[$fieldName]['field_req'] = 0;
			}
		}
	}



	// Amend the list of action tags (accessible from the add/edit field window in the instrument
	// designer) when features which provide extra action tags are enabled.

	public function redcap_every_page_top()
	{
		if ( substr( PAGE_FULL, strlen( APP_PATH_WEBROOT ), 26 ) == 'Design/online_designer.php' ||
		     substr( PAGE_FULL, strlen( APP_PATH_WEBROOT ), 22 ) == 'ProjectSetup/index.php' )
		{
			$listActionTags = [];
			if ( $this->getSystemSetting( 'enable-regex' ) )
			{
				$listActionTags['@REGEX'] =
					'Validate a field according to a regular expression. The format must follow ' .
					'the pattern @REGEX=\'????\', in which the pattern is inside single or ' .
					'double quotes.';
			}
			if ( $this->getProjectSetting( 'no-future-dates' ) )
			{
				$listActionTags['@ALLOWFUTURE'] =
					'For a date or datetime field, override the validation prohibiting dates in ' .
					'the future (after current date).';
			}
			if ( $this->getProjectSetting( 'no-past-dates' ) )
			{
				$listActionTags['@ALLOWPAST'] =
					'For a date or datetime field, override the validation prohibiting dates in ' .
					'the past (before defined date in module settings).';
			}
			$this->provideActionTagExplain( $listActionTags );
		}
	}



	// Provide the features on data entry forms (not surveys).

	public function redcap_data_entry_form_top( $project_id, $record=null, $instrument, $event_id,
	                                            $group_id=null, $repeat_instance=1 )
	{
		$this->outputDateValidation( $instrument, $record, $event_id );
		$this->outputRegexValidation( $instrument );
		$this->outputEnforceValidation( $instrument );
	}



	// Provide the features on surveys.

	public function redcap_survey_page_top( $project_id, $record=null, $instrument, $event_id,
	                                        $group_id=null, $survey_hash=null, $response_id=null,
	                                        $repeat_instance=1 )
	{
		$this->outputDateValidation( $instrument, $record, $event_id );
		$this->outputRegexValidation( $instrument );
		$this->outputSurveyValidationSkip( $instrument );
	}



	// Amend date field validation to block past/future dates as required.

	protected function outputDateValidation( $instrument, $record, $eventID )
	{
		$blockFutureDates = $this->getProjectSetting( 'no-future-dates' );
		$blockPastDates = $this->getProjectSetting( 'no-past-dates' );

		// Stop here if date validation disabled.
		if ( ! $blockFutureDates && ! $blockPastDates )
		{
			return;
		}

		$listDateFields = [];
		$listFormFields = \REDCap::getDataDictionary( 'array', false, true, $instrument );
		$projectEarliestDate = '';
		$recordEarliestDate = '';

		// If blocking 'past' dates, determine the earliest date allowed.
		if ( $blockPastDates )
		{
			// Get the earliest date for the project, if defined in the module project settings.
			if ( $this->getProjectSetting( 'past-date' ) != '' )
			{
				$projectEarliestDate = $this->getProjectSetting( 'past-date' );
				if ( strlen( $projectEarliestDate ) == 10 )
				{
					$projectEarliestDate .= ' 00:00:00';
				}
				elseif ( strlen( $projectEarliestDate ) == 16 )
				{
					$projectEarliestDate .= ':00';
				}
			}

			// Determine the earliest date for the record, based on the defined event/field.
			$earliestDateEvent = $this->getProjectSetting( 'past-date-event' );
			$earliestDateField = $this->getProjectSetting( 'past-date-field' );
			if ( $earliestDateEvent != '' && $earliestDateField != '' )
			{
				$recordEarliestDate =
					\REDCap::getData( 'array', $record, $earliestDateField, $earliestDateEvent )
						[ $record ][ $earliestDateEvent ][ $earliestDateField ] ?? '';
				$recordEarliestDate = array_reduce( [ $recordEarliestDate ],
				                                    function( $c, $i ) { return $c . $i; }, '' );
				if ( $recordEarliestDate != '' &&
				     preg_match( '/^[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])/',
				                 $recordEarliestDate ) )
				{
					if ( strlen( $recordEarliestDate ) == 10 )
					{
						$recordEarliestDate .= ' 00:00:00';
					}
					elseif ( strlen( $recordEarliestDate ) == 16 )
					{
						$recordEarliestDate .= ':00';
					}
				}
				else
				{
					$recordEarliestDate = '';
				}
			}
		}

		// Apply the validation to each date field on the form as required.
		foreach ( $listFormFields as $fieldName => $infoField )
		{
			$noFutureDate = ( $blockFutureDates &&
			                !preg_match( '/@ALLOWFUTURE(\s|$)/', $infoField['field_annotation'] ) );
			$noPastDate = ( $blockPastDates &&
			                !preg_match( '/@ALLOWPAST(\s|$)/', $infoField['field_annotation'] ) );
			if ( $infoField[ 'field_type' ] == 'text' &&
			     ( substr( $infoField[ self::VTYPE ], 0, 5 ) == 'date_' ||
			       substr( $infoField[ self::VTYPE ], 0, 9 ) == 'datetime_' ) &&
			     ( $noFutureDate || $noPastDate ) )
			{
				if ( substr( $infoField[ self::VTYPE ], 0, 17 ) == 'datetime_seconds_' )
				{
					$fieldLength = 19;
				}
				elseif ( substr( $infoField[ self::VTYPE ], 0, 9 ) == 'datetime_' )
				{
					$fieldLength = 16;
				}
				else
				{
					$fieldLength = 10;
				}

				$notBefore = '';

				if ( $noPastDate && ! in_array( $infoField[ self::VMIN ], [ 'now', 'today' ] ) &&
				     substr( trim( $infoField[ self::VMIN ] ), 0, 1 ) != '[' )
				{
					$notBefore = $projectEarliestDate;
					if ( ( $notBefore == '' || $notBefore < $recordEarliestDate ) &&
					     ( $earliestDateEvent != $eventID || $earliestDateField != $fieldName ) )
					{
						$notBefore = $recordEarliestDate;
					}
				}

				$listDateFields[ $fieldName ] = [ 'type' => $infoField[ self::VTYPE ],
				                                  'len' => $fieldLength,
				                                  'nofuture' => $noFutureDate,
				                                  'notbefore' => $notBefore ];
			}
		}

		if ( count( $listDateFields ) > 0 )
		{
			if ( \REDCap::versionCompare( REDCAP_VERSION, '12.1.0' ) >= 0 )
			{
				$newRC = 'true';
				$nowJS = "new Date( vNow.getTime() - ( vNow.getTimezoneOffset() * 60000 ) )\n";
			}
			else
			{
				$newRC = 'false';
				$nowJS =
					"new Date( vNow.getTime() + 900000 - ( vNow.getTimezoneOffset() * 60000 ) )\n";
			}


			// Output JavaScript to apply the date validation.
?>
<script type="text/javascript">
$(function()
{
  var vFields = JSON.parse('<?php echo json_encode($listDateFields); ?>')
  var vNow = new Date()
  vNow = <?php echo $nowJS; ?>
  vNow = vNow.toISOString().replace( 'T', ' ' )
  Object.keys( vFields ).forEach( function( vFieldName )
  {
    var vFieldObj = $('input[name="' + vFieldName + '"]')
    if ( vFieldObj.length < 1 )
    {
      return
    }
    vFieldObj = vFieldObj[0]
    var vFieldData = vFields[ vFieldName ]
    var vOldBlur = vFieldObj.onblur
    var vNotBefore = ''
    var vNotAfter = ''
    if ( vFieldData.notbefore != '' )
    {
      vNotBefore = vFieldData.notbefore.slice( 0, vFieldData.len )
    }
    if ( vFieldData.nofuture )
    {
      vNotAfter = vNow.slice( 0, vFieldData.len )
    }
    if ( vOldBlur === null )
    {
      vNotAfter = ( <?php echo $newRC; ?> && vFieldData.nofuture ? 'now' : vNotAfter )
      vFieldObj.onblur = function()
      {
        redcap_validate( this, vNotBefore, vNotAfter, 'hard', vFieldData.type, 1 )
      }
    }
    else
    {
      var vFuncStrParts = vOldBlur.toString().match(/redcap_validate\((.*?,)(.*?),(.*?)(,.*)\)/)
      var vFuncStrStart = vFuncStrParts[1]
      var vEarliest = vFuncStrParts[2]
      var vEarliestVal = vEarliest.match(/^(.*: *)?'(.*)'\)?$/)[2]
      var vLatest = vFuncStrParts[3]
      var vLatestVal = vLatest.match(/^(.*: *)?'(.*)'\)?$/)[2]
      var vFuncStrEnd = vFuncStrParts[4]
      if ( vNotBefore != '' && vEarliestVal.localeCompare( vNotBefore ) < 0 )
      {
        vEarliest = "'" + vNotBefore + "'"
      }
      if ( vLatestVal == '' || ( vNotAfter != '' && vLatestVal != 'today' && vLatestVal != 'now' ) )
      {
        if ( <?php echo $newRC; ?> && vFieldData.nofuture )
        {
          if ( vLatestVal == '' )
          {
            vLatest = ( vFieldData.len == 10 ? "'today'" : "'now'" )
          }
          else
          {
            vLatest = "(" + vLatest + ".localeCompare('" + vNotAfter + "')>0?" +
                      ( vFieldData.len == 10 ? 'today' : 'now' ) + ":" + vLatest + ")"
          }
        }
        else if ( ( vLatestVal == '' && vNotAfter != '' ) ||
                  vLatestVal.localeCompare( vNotAfter ) > 0 )
        {
          vLatest = "'" + vNotAfter + "'"
        }
      }
      vFieldObj.onblur = new Function( 'redcap_validate(' + vFuncStrStart + vEarliest +
                                       "," + vLatest + vFuncStrEnd + ')' )
    }
  })
})
</script>
<?php


		}
	}



	// Output JavaScript to prevent form submission (with status = complete) when a field contains
	// an invalid value, or a required field has not been completed.

	protected function outputEnforceValidation( $instrument )
	{
		// Stop here if validation enforcement is disabled.
		if ( ! $this->getProjectSetting( 'enforce-validation' ) )
		{
			return;
		}

		// Check if any enforcement exemptions apply to this form.
		$checkReqFields = true;
		$checkValues = true;
		if ( $this->getProjectSetting( 'enforce-validation-exempt-forms' ) )
		{
			$listExemptForms = $this->getProjectSetting( 'enforce-validation-exempt-form' );
			$listExemptModes = $this->getProjectSetting( 'enforce-validation-exempt-mode' );
			for ( $i = 0; $i < count( $listExemptForms ); $i++ )
			{
				if ( $listExemptForms[$i] == $instrument )
				{
					if ( $listExemptModes[$i] == 'A' )
					{
						// Exempt from all validation enforcement.
						return;
					}
					if ( $listExemptModes[$i] == 'R' )
					{
						// Exempt from required field enforcement.
						$checkReqFields = false;
					}
					if ( $listExemptModes[$i] == 'V' )
					{
						// Exempt from value validation enforcement.
						$checkValues = false;
					}
					break;
				}
			}
		}

		// Identify required fields.
		$reqFields = '';
		if ( $checkReqFields )
		{
			$listFormFields = \REDCap::getDataDictionary( 'array', false, true, $instrument );
			foreach ( $listFormFields as $fieldName => $infoField )
			{
				if ( $infoField['required_field'] == 'y' )
				{
					switch ( $infoField['field_type'] )
					{
						case 'notes':
							$fieldSelector = 'textarea[name="' . $fieldName . '"]';
							break;
						case 'dropdown':
						case 'sql':
							$fieldSelector = 'select[name="' . $fieldName . '"]';
							break;
						case 'checkbox':
							$fieldSelector = 'input[name="__chkn__' . $fieldName . '"]';
							break;
						default:
							$fieldSelector = 'input[name="' . $fieldName . '"]';
							break;
					}
					$reqFields .= ( ( $reqFields == '' ) ? '' : ', ' ) . '#' . $fieldName .
					              '-tr:visible ' . $fieldSelector . ', span.rc-field-embed[var="' .
					              $fieldName . '"]:visible ' . $fieldSelector;
				}
			}
		}

		// Generate the alert text.
		$enforceText = 'Please ' . ( $checkReqFields ? 'answer all required fields ' : '' ) .
		               ( $checkReqFields && $checkValues ? 'and ' : '' ) .
		               ( $checkValues ? 'fix any field validation errors ' : '' ) .
		               'before submitting the form as complete.';
		$enforceText = json_encode( $enforceText );

		// Output JavaScript.
?>
<script type="text/javascript">
$(function()
{
  $('#valtext_rangesoft2').text('Please check.')
  $('input:visible, textarea:visible, select:visible').each( function()
  {
    if ( typeof this.onblur == 'function' )
    {
      this.onblur.call( this )
    }
  })
  var vFnEnforceValidation = function()
  {
    if ( ['','0','1'].includes( $('select[name="<?php echo $instrument; ?>_complete"]').val() ) )
    {
      return true
    }
    if ( <?php echo $checkValues ? 'true' : 'false'; ?> &&
         $('input[style*="background-color: rgb(255, 183, 190)"]:visible, ' +
           'textarea[style*="background-color: rgb(255, 183, 190)"]:visible, ' +
           'select[style*="background-color: rgb(255, 183, 190)"]:visible').length > 0 )
    {
      return false
    }
    var vIsValid = true
    var vCheckboxValid = {}
    var vCheckboxNames = []
    $('<?php echo $reqFields; ?>').each( function()
    {
      if ( $(this).attr('type') == 'checkbox' )
      {
        if ( vCheckboxValid[ $(this).attr('name') ] === undefined )
        {
          vCheckboxValid[ $(this).attr('name') ] = false
          vCheckboxNames.push( $(this).attr('name') )
        }
        if ( $(this).prop('checked') )
        {
          vCheckboxValid[ $(this).attr('name') ] = true
        }
      }
      else if ( $(this).val() === '' )
      {
        vIsValid = false
      }
    })
    if ( vIsValid )
    {
      vCheckboxNames.forEach( function( vCheckboxName )
      {
        if ( ! vCheckboxValid[ vCheckboxName ] &&
             $( '#' + vCheckboxName.substring(8) + '_MDLabel' ).text() == '' )
        {
          vIsValid = false
        }
      })
    }
    return vIsValid
  }

  var vFnOldDataEntrySubmit = dataEntrySubmit
  dataEntrySubmit = function ( ob )
  {
    if ( ! vFnEnforceValidation() )
    {
      simpleDialog( <?php echo $enforceText; ?> )
      return false
    }
    return vFnOldDataEntrySubmit( ob )
  }
})
</script>
<?php


	}



	// Output JavaScript to perform regular expression validation on fields.

	protected function outputRegexValidation( $instrument )
	{
		// Stop here if regular expression validation is disabled.
		if ( ! $this->getSystemSetting( 'enable-regex' ) )
		{
			return;
		}

		// Get and check the regular expressions for each field. Any invalid regular expressions
		// will be ignored.
		$listRegexFields = [];
		$listFormFields = \REDCap::getDataDictionary( 'array', false, true, $instrument );

		foreach ( $listFormFields as $fieldName => $infoField )
		{
			if ( in_array( $infoField[ 'field_type' ], [ 'text', 'notes' ] ) &&
			     $infoField[ self::VTYPE ] == '' )
			{
				$hasRegex = preg_match( "/(^|\\s)@REGEX=((\'[^\'\r\n]*\')|(\"[^\"\r\n]*\"))(\\s|$)/",
				                        $infoField['field_annotation'], $regexMatches );
				if ( $hasRegex )
				{
					$fieldRegex = substr( $regexMatches[ 2 ], 1, -1 );
					$validRegex = ( preg_match( $regexMatches[ 2 ], '' ) !== false );
					if ( $validRegex && $fieldRegex != '' )
					{
						$listRegexFields[ $fieldName ] = [ 'regex' =>$fieldRegex,
						                                   'type' => $infoField[ 'field_type' ] ];
					}
				}
			}
		}

		if ( count( $listRegexFields ) > 0 )
		{


			// Output JavaScript.
?>
<script type="text/javascript">
$(function()
{
  var vFuncRegexValidate = function ( vElem, vPattern )
  {
    var vRegex = new RegExp( vPattern )
    if ( vElem.value == '' || vRegex.test( vElem.value ) )
    {
      vElem.style.fontWeight = 'normal'
      vElem.style.backgroundColor = '#FFFFFF'
    }
    else
    {
      var vPopupID = 'redcapValidationErrorPopup'
      var vPopupMsg = 'The value you provided could not be validated because it does not follow ' +
                      'the expected format. Please try again.'
      $('#' + vPopupID).remove()
      initDialog( vPopupID )
      $('#' + vPopupID).html(vPopupMsg)
      setTimeout( function()
      {
        simpleDialog( vPopupMsg, null, vPopupID, null, '' )
      }, 20 )
      vElem.style.fontWeight = 'bold'
      vElem.style.backgroundColor = '#FFB7BE'
    }
  }
  var vFields = JSON.parse('<?php echo addslashes( json_encode($listRegexFields) ); ?>')
  Object.keys( vFields ).forEach( function( vFieldName )
  {
    var vFieldData = vFields[ vFieldName ]
    if ( vFieldData.type == 'notes' )
    {
      var vFieldObj = $('textarea[name="' + vFieldName + '"]')
    }
    else
    {
      var vFieldObj = $('input[name="' + vFieldName + '"]')
    }
    if ( vFieldObj.length < 1 )
    {
      return
    }
    vFieldObj = vFieldObj[0]
    vFieldObj.onblur = function() { vFuncRegexValidate( this, vFieldData.regex ) }
  })
})
</script>
<?php


		}
	}



	// Output JavaScript to provide a 'continue anyway' link on surveys when an error message
	// appears due to incomplete required fields.

	protected function outputSurveyValidationSkip( $instrument )
	{
		// Stop here if skip required fields validation not enabled.
		if ( ! $this->getProjectSetting( 'survey-skip-validate' ) )
		{
			return;
		}

		// Stop here if the survey is exempted from this feature.
		if ( $this->getProjectSetting( 'survey-skip-validate-exempt-forms' ) )
		{
			if ( in_array( $instrument,
			               $this->getProjectSetting( 'survey-skip-validate-exempt-form' ) ) )
			{
				return;
			}
		}


		// Output JavaScript.
?>
<script type="text/javascript">
$(function()
{
  var vDialogCount = 0
  var vDialogTimer = setInterval( function()
  {
    var vDialogBottom = $('div[aria-describedby="reqPopup"] div.ui-dialog-buttonpane')
    if ( vDialogBottom.length > 0 )
    {
      clearInterval( vDialogTimer )
      var vContinueLink = $('<a href="#" style="color:#6d6d88">Continue anyway...</a>')
      vContinueLink.on('click', function()
      {
        var vForm = $('#form')
        if ( confirm( 'WARNING: Some questions have not been answered.\n\n' +
                      'It is highly recommended that you cancel now and ' +
                      'complete every question.\n\n' +
                      'Are you sure you want to continue?' ) )
        {
          vForm.attr('action', vForm.attr('action') + '&__skipvalidate=1')
          vForm.submit()
        }
        return false
      })
      var vContinueDiv = $('<div style="float:left;margin-top:10px;margin-left:5px"></div>')
      vContinueDiv.prepend( vContinueLink )
      vDialogBottom.prepend( vContinueDiv )
    }
    else if ( vDialogCount > 6 )
    {
      clearInterval( vDialogTimer )
    }
    vDialogCount++
  }, 300)
})
</script>
<?php

	}





	// Output JavaScript to amend the action tags guide.

	function provideActionTagExplain( $listActionTags )
	{
		if ( empty( $listActionTags ) )
		{
			return;
		}
		$listActionTagsJS = [];
		foreach ( $listActionTags as $t => $d )
		{
			$listActionTagsJS[] = [ $t, $d ];
		}
		$listActionTagsJS = json_encode( $listActionTagsJS );

?>
<script type="text/javascript">
$(function()
{
  var vActionTagPopup = actionTagExplainPopup
  var vMakeRow = function(vTag, vDesc, vTable)
  {
    var vRow = $( '<tr>' + vTable.find('tr:first').html() + '</tr>' )
    var vOldTag = vRow.find('td:eq(1)').html()
    var vButton = vRow.find('button')
    vRow.find('td:eq(1)').html(vTag)
    vRow.find('td:eq(2)').html(vDesc)
    if ( vButton.length != 0 )
    {
      vButton.attr('onclick', vButton.attr('onclick').replace(vOldTag,vTag))
    }
    var vRows = vTable.find('tr')
    var vInserted = false
    for ( var i = 0; i < vRows.length; i++ )
    {
      var vA = vRows.eq(i).find('td:eq(1)').html()
      if ( vTag < vRows.eq(i).find('td:eq(1)').html() )
      {
        vRows.eq(i).before(vRow)
        vInserted = true
        break
      }
    }
    if ( ! vInserted )
    {
      vRows.last().after(vRow)
    }
  }
  actionTagExplainPopup = function(hideBtns)
  {
    vActionTagPopup(hideBtns)
    var vCheckTagsPopup = setInterval( function()
    {
      if ( $('div[aria-describedby="action_tag_explain_popup"]').length == 0 )
      {
        return
      }
      clearInterval( vCheckTagsPopup )
      var vActionTagTable = $('#action_tag_explain_popup table');
      <?php echo $listActionTagsJS; ?>.forEach(function(vItem)
      {
        vMakeRow(vItem[0],vItem[1],vActionTagTable)
      })
    }, 200 )
  }
})
</script>
<?php

	}



	// Module settings validation.

	public function validateSettings( $settings )
	{
		$errMsg = '';

		if ( $settings['enforce-validation'] && $settings['enforce-validation-exempt-forms'] )
		{
			$listExemptForms = [];
			for ( $i = 0; $i < count( $settings['enforce-validation-exempt'] ); $i++ )
			{
				if ( $settings['enforce-validation-exempt-form'][$i] == '' )
				{
					$errMsg .= "\n- Exempt form (from validation enforcement) " . ($i+1) .
					           " is missing.";
				}
				elseif ( in_array( $settings['enforce-validation-exempt-form'][$i],
				                   $listExemptForms ) )
				{
					$errMsg .= "\n- Exempt form (from validation enforcement) " . ($i+1) .
					           " is a duplicate.";
				}
				else
				{
					$listExemptForms[] = $settings['enforce-validation-exempt-form'][$i];
				}
				if ( $settings['enforce-validation-exempt-mode'][$i] == '' )
				{
					$errMsg .= "\n- Exemption mode (from validation enforcement) " . ($i+1) .
					           " is missing.";
				}
			}
		}

		if ( $settings['no-past-dates'] )
		{
			if ( ( $settings['past-date-event'] == '' && $settings['past-date-field'] != '' ) ||
			     ( $settings['past-date-event'] != '' && $settings['past-date-field'] == '' ) )
			{
				$errMsg .= "\n- To determine past dates by field, both event and field must be " .
				           "specified.";
			}
			if ( $settings['past-date-event'] == '' && $settings['past-date'] == '' )
			{
				$errMsg .= "\n- To disallow entry of dates in the past, either an event/field " .
				           "or a fixed date must be specified to determine past dates.";
			}
			if ( $settings['past-date'] != '' &&
			     ! preg_match( '/^[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/',
			                   $settings['past-date'] ) )
			{
				$errMsg .= "\n- Invalid date entered for determining past dates.";
			}
		}

		if ( $settings['survey-skip-validate'] && $settings['survey-skip-validate-exempt-forms'] )
		{
			$listExemptForms = [];
			for ( $i = 0; $i < count( $settings['survey-skip-validate-exempt-form'] ); $i++ )
			{
				if ( $settings['survey-skip-validate-exempt-form'][$i] == '' )
				{
					$errMsg .= "\n- Exempt form (from survey continue) " . ($i+1) . " is missing.";
				}
				elseif ( in_array( $settings['survey-skip-validate-exempt-form'][$i],
				                   $listExemptForms ) )
				{
					$errMsg .= "\n- Exempt form (from survey continue) " . ($i+1) .
					           " is a duplicate.";
				}
				else
				{
					$listExemptForms[] = $settings['survey-skip-validate-exempt-form'][$i];
				}
			}
		}

		if ( $errMsg != '' )
		{
			return "Your configuration contains errors:$errMsg";
		}

		return null;
	}


}

