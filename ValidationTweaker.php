<?php

namespace Nottingham\ValidationTweaker;

class ValidationTweaker extends \ExternalModules\AbstractExternalModule
{
	const VTYPE = 'text_validation_type_or_show_slider_number';
	const VMIN = 'text_validation_min';



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
			$listRemoveActionTags = [];
			if ( ! $this->getSystemSetting( 'enable-regex' ) )
			{
				$listRemoveActionTags[] = '@REGEX';
			}
			{
			}
			if ( ! empty( $listRemoveActionTags ) )
			{
				$this->provideActionTagRemove( $listRemoveActionTags );
			}
		}
	}



	// Provide the features on data entry forms (not surveys).

	public function redcap_data_entry_form_top( $project_id, $record=null, $instrument, $event_id,
	                                            $group_id=null, $repeat_instance=1 )
	{
		$this->outputDateValidation( $instrument, $record, $event_id, $repeat_instance );
		$this->outputLogicRegexValidation( $instrument, $record, $event_id, $repeat_instance );
		$this->outputEnforceValidation( $instrument );
	}



	// Provide the features on surveys.

	public function redcap_survey_page_top( $project_id, $record=null, $instrument, $event_id,
	                                        $group_id=null, $survey_hash=null, $response_id=null,
	                                        $repeat_instance=1 )
	{
		$this->outputDateValidation( $instrument, $record, $event_id, $repeat_instance );
		$this->outputLogicRegexValidation( $instrument, $record, $event_id, $repeat_instance );
		$this->outputSurveyValidationSkip( $instrument );
	}



	// Amend date field validation to block future dates as required.

	protected function outputDateValidation( $instrument, $record, $eventID, $instance )
	{
		$blockFutureDates = $this->getProjectSetting( 'no-future-dates' );

		// Stop here if date validation disabled.
		if ( ! $blockFutureDates )
		{
			return;
		}

		$listDateFields = [];
		$listFormFields = \REDCap::getDataDictionary( 'array', false, true, $instrument );

		// Apply the validation to each date field on the form as required.
		foreach ( $listFormFields as $fieldName => $infoField )
		{
			// Skip non date/datetime fields.
			if ( $infoField[ 'field_type' ] != 'text' ||
			     ( substr( $infoField[ self::VTYPE ], 0, 5 ) != 'date_' &&
			       substr( $infoField[ self::VTYPE ], 0, 9 ) != 'datetime_' ) )
			{
				continue;
			}
			$annotation = \Form::replaceIfActionTag( $infoField[ 'field_annotation' ],
			                                         $this->getProjectId(), $record,
			                                         $eventID, $instrument, $instance );
			$noFutureDate = !preg_match( '/@ALLOWFUTURE(\s|$)/', $annotation );
			if ( $noFutureDate )
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

				$listDateFields[ $fieldName ] = [ 'type' => $infoField[ self::VTYPE ],
				                                  'len' => $fieldLength ];
			}
		}

		if ( count( $listDateFields ) > 0 )
		{


			// Output JavaScript to apply the date validation.
?>
<script type="text/javascript">
$(function()
{
  var vFields = JSON.parse('<?php echo json_encode($listDateFields); ?>')
  var vNow = new Date()
  vNow = new Date( vNow.getTime() - ( vNow.getTimezoneOffset() * 60000 ) )
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
    if ( vOldBlur === null )
    {
      vFieldObj.onblur = function()
      {
        redcap_validate( this, '', 'now', 'hard', vFieldData.type, 1 )
      }
    }
    else
    {
      var vNotAfter = vNow.slice( 0, vFieldData.len )
      var vFuncStrParts = vOldBlur.toString().match(/redcap_validate\((.*?,)(.*?),(.*?)(,.*)\)/)
      var vFuncStrStart = vFuncStrParts[1]
      var vEarliest = vFuncStrParts[2]
      var vLatest = vFuncStrParts[3]
      var vLatestVal = vLatest.match(/^(.*: *)?'(.*)'\)?$/)[2]
      var vFuncStrEnd = vFuncStrParts[4]
      if ( vLatestVal == '' )
      {
        vLatest = ( vFieldData.len == 10 ? "'today'" : "'now'" )
      }
      else if ( vNotAfter != '' && vLatestVal != 'today' && vLatestVal != 'now' )
      {
        vLatest = "(" + vLatest + ".localeCompare('" + vNotAfter + "')>0?" +
                  ( vFieldData.len == 10 ? 'today' : 'now' ) + ":" + vLatest + ")"
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



	// Output JavaScript to perform logic and regular expression validation on fields.

	protected function outputLogicRegexValidation( $instrument, $record, $eventID, $instance )
	{
		// Check if regular expression validation is enabled.
		$allowedRegex = $this->getSystemSetting( 'enable-regex' );

		// Get and check the regular expressions for each field. Any invalid regular expressions
		// will be ignored.
		$listLogicFields = [];
		$listFormFields = \REDCap::getDataDictionary( 'array', false, true, $instrument );

		foreach ( $listFormFields as $fieldName => $infoField )
		{
			if ( in_array( $infoField[ 'field_type' ], [ 'text', 'notes' ] ) &&
			     $infoField[ self::VTYPE ] == '' )
			{
				$annotation = \Form::replaceIfActionTag( $infoField[ 'field_annotation' ],
				                                         $this->getProjectId(), $record,
				                                         $eventID, $instrument, $instance );
				$hasRegex = false;
				$fieldRegex = '';
				if ( $allowedRegex )
				{
					$hasRegex = preg_match( "/(^|\\s)@REGEX=((\'[^\'\r\n]*\')|" .
					                        "(\"[^\"\r\n]*\"))(\\s|$)/",
					                        $annotation, $regexMatches );
					$fieldRegex = substr( $regexMatches[ 2 ], 1, -1 );
					$validRegex = ( preg_match( $regexMatches[ 2 ], '' ) !== false );
					if ( ! $validRegex )
					{
						$hasRegex = false;
						$fieldRegex = '';
					}
				}
				$fieldLogic =
						\Form::getValueInParenthesesActionTag( $annotation, '@VALIDATE-LOGIC' );
				if ( $hasRegex || $fieldLogic != '' )
				{
					if ( $fieldLogic != '' )
					{
						$fieldLogic = \LogicTester::formatLogicToJS( $fieldLogic, false, $eventID,
						                                             false, $this->getProjectId() );
					}
					$message = \Form::getValueInQuotesActionTag( $annotation, '@VALIDATE-MESSAGE' );
					$listLogicFields[ $fieldName ] = [ 'regex' => $fieldRegex,
					                                   'logic' => $fieldLogic,
					                                   'type' => $infoField[ 'field_type' ],
					                                   'message' => $message ];
				}
			}
		}

		if ( count( $listLogicFields ) > 0 )
		{


			// Output JavaScript.
?>
<script type="text/javascript">
$(function()
{
  var vFuncValidate = function ( vElem, vPattern, vLogic, vMessage )
  {
    var vRegex = new RegExp( vPattern )
    var vLogicResult = vLogic == '' ? true : (new Function('return ' + vLogic))()
    if ( vElem.value == '' || ( vLogicResult && ( vPattern == '' || vRegex.test( vElem.value ) ) ) )
    {
      vElem.style.fontWeight = 'normal'
      vElem.style.backgroundColor = '#FFFFFF'
    }
    else
    {
      var vPopupID = 'redcapValidationErrorPopup'
      var vPopupMsg = 'The value you provided could not be validated because it does not follow ' +
                      'the expected format. Please try again.'
      if ( vMessage != '' )
      {
        vPopupMsg = vMessage
      }
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
  var vFields = JSON.parse('<?php echo addslashes( json_encode($listLogicFields) ); ?>')
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
    vFieldObj.onblur = function()
    {
      vFuncValidate( this, vFieldData.regex, vFieldData.logic, vFieldData.message )
    }
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





	// Output JavaScript to remove action tags from the action tags guide.

	function provideActionTagRemove( $listActionTags )
	{

?>
<script type="text/javascript">
$(function()
{
  var vListTagsRemove = <?php echo json_encode( $listActionTags ), "\n"; ?>
  var vActionTagPopup = actionTagExplainPopup
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
      var vRows = vActionTagTable.find('tr')
      for ( var i = 0; i < vRows.length; i++ )
      {
        var vTag = vRows.eq(i).find('td:eq(1)').text()
        if ( vListTagsRemove.includes( vTag ) )
        {
          vRows.eq(i).css('display','none')
        }
        if ( vTag == '@VALIDATE-MESSAGE' && vListTagsRemove.includes( '@REGEX' ) )
        {
          var vDescription = vRows.eq(i).find('td:eq(2)').html()
          vDescription = vDescription.replace('regular expression (@REGEX) or ','')
          vRows.eq(i).find('td:eq(2)').html( vDescription )
        }
      }
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

