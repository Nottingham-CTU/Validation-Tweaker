<?php

namespace Nottingham\ValidationTweaker;

class ValidationTweaker extends \ExternalModules\AbstractExternalModule
{
	const VTYPE = 'text_validation_type_or_show_slider_number';



	function redcap_every_page_before_render()
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



	function redcap_every_page_top()
	{
		if ( substr( PAGE_FULL, strlen( APP_PATH_WEBROOT ), 26 ) == 'Design/online_designer.php' &&
		     ( $this->getSystemSetting( 'enable-regex' ) ||
		       $this->getProjectSetting( 'no-future-dates' ) ||
		       $this->getProjectSetting( 'no-past-dates' ) ) )
		{
?>
<script type="text/javascript">
$(function()
{
  var vActionTagPopup = actionTagExplainPopup
  var vMakeRow = function( tag, desc, position, insertAfter = false )
  {
    var vRow = $( '<tr>' + $('tr:has(td.nowrap:contains("' + position + '")):eq(0)').html() + '</tr>' )
    vRow.find('td:eq(1)').html( tag )
    vRow.find('td:eq(2)').html( desc )
    vRow.find('button').attr('onclick', vRow.find('button').attr('onclick').replace(position,tag))
    if ( insertAfter )
    {
      $('tr:has(td.nowrap:contains("' + position + '")):eq(0)').after( vRow )
    }
    else
    {
      $('tr:has(td.nowrap:contains("' + position + '")):eq(0)').before( vRow )
    }
  }
  actionTagExplainPopup = function( hideBtns )
  {
    vActionTagPopup( hideBtns )
    var vCheckTagsPopup = setInterval( function()
    {
      if ( $('div[aria-describedby="action_tag_explain_popup"]').length == 0 )
      {
        return
      }
      clearInterval( vCheckTagsPopup )
<?php

			if ( $this->getSystemSetting( 'enable-regex' ) )
			{

?>
      vMakeRow( '@REGEX', 'Validate a field according to a regular expression. The format must ' +
                          'follow the pattern @REGEX=\'????\', in which the pattern is inside ' +
                          'single or double quotes.',
                          '@READONLY-SURVEY', true )
<?php

			}
			if ( $this->getProjectSetting( 'no-future-dates' ) )
			{

?>
      vMakeRow( '@ALLOWFUTURE', 'For a date or datetime field, override the validation ' +
                                'prohibiting dates in the future (after current date).',
                                '@CHARLIMIT' )
<?php

			}
			if ( $this->getProjectSetting( 'no-past-dates' ) )
			{

?>
      vMakeRow( '@ALLOWPAST', 'For a date or datetime field, override the validation prohibiting ' +
                              'dates in the past (before defined date in module settings).',
                              '@CHARLIMIT' )
<?php

			}

?>
    }, 100 )
  }
})
</script>
<?php
		}
	}



	public function redcap_data_entry_form_top( $project_id, $record=null, $instrument, $event_id,
	                                            $group_id=null, $repeat_instance=1 )
	{
		$this->outputDateValidation( $instrument, $record, $event_id );
		$this->outputRegexValidation( $instrument );
		$this->outputEnforceValidation( $instrument );
	}



	public function redcap_survey_page_top( $project_id, $record=null, $instrument, $event_id,
	                                        $group_id=null, $survey_hash=null, $response_id=null,
	                                        $repeat_instance=1 )
	{
		$this->outputDateValidation( $instrument, $record, $event_id );
		$this->outputRegexValidation( $instrument );
		$this->outputSurveyValidationSkip();
	}



	protected function outputDateValidation( $instrument, $record, $eventID )
	{
		$blockFutureDates = $this->getProjectSetting( 'no-future-dates' );
		$blockPastDates = $this->getProjectSetting( 'no-past-dates' );
		$listDateFields = [];
		$listFormFields = \REDCap::getDataDictionary( 'array', false, true, $instrument );
		$projectEarliestDate = '';
		$recordEarliestDate = '';

		if ( $blockPastDates )
		{
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

			$earliestDateEvent = $this->getProjectSetting( 'past-date-event' );
			$earliestDateField = $this->getProjectSetting( 'past-date-field' );
			if ( $earliestDateEvent != '' && $earliestDateField != '' )
			{
				$recordEarliestDate =
					\REDCap::getData( 'array', $record, $earliestDateField, $earliestDateEvent )
						[ $record ][ $earliestDateEvent ][ $earliestDateField ];
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

				if ( $noPastDate )
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
    var vFieldObj = $('input[name="' + vFieldName + '"]')[0]
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
      vFieldObj.onblur = function()
      {
        redcap_validate( this, vNotBefore, vNotAfter, 'hard', vFieldData.type, 1 )
      }
    }
    else
    {
      var vFuncStrParts = vOldBlur.toString().match(/redcap_validate\((.*?,)'(.*?)','(.*?)'(,.*)\)/)
      var vFuncStrStart = vFuncStrParts[1]
      var vEarliest = vFuncStrParts[2]
      var vLatest = vFuncStrParts[3]
      var vFuncStrEnd = vFuncStrParts[4]
      if ( vEarliest.localeCompare( vNotBefore ) < 0 )
      {
        vEarliest = vNotBefore
      }
      if ( vLatest == '' || ( vNotAfter != '' && vLatest.localeCompare( vNotAfter ) > 0 ) )
      {
        vLatest = vNotAfter
      }
      vFieldObj.onblur = new Function( 'redcap_validate(' + vFuncStrStart + "'" + vEarliest +
                                       "','" + vLatest + "'" + vFuncStrEnd + ')' )
    }
  })
})
</script>
<?php


		}
	}



	protected function outputEnforceValidation( $instrument )
	{
		if ( ! $this->getProjectSetting( 'enforce-validation' ) )
		{
			return;
		}

		$listFormFields = \REDCap::getDataDictionary( 'array', false, true, $instrument );
		$reqFields = '';
		foreach ( $listFormFields as $fieldName => $infoField )
		{
			if ( $infoField['required_field'] == 'y' )
			{
				$reqFields .= ( $reqFields == '' ) ? '' : ', ';
				switch ( $infoField['field_type'] )
				{
					case 'notes':
						$reqFields .= 'textarea';
						break;
					case 'dropdown':
						$reqFields .= 'select';
						break;
					default:
						$reqFields .= 'input';
						break;
				}
				$reqFields .= '[name="' . $fieldName . '"]:visible';
			}
		}


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
    if ( $('input[style*="background-color: rgb(255, 183, 190)"]:visible, ' +
           'textarea[style*="background-color: rgb(255, 183, 190)"]:visible, ' +
           'select[style*="background-color: rgb(255, 183, 190)"]:visible').length > 0 )
    {
      return false
    }
    var vIsValid = true
    $('<?php echo $reqFields; ?>').each( function()
    {
      if ( $(this).val() === '' )
      {
        vIsValid = false
        return
      }
    })
    return vIsValid
  }

  $('[id^="submit-btn-save"]').each( function()
  {
    $(this).data('click', this.onclick)
    this.onclick = function( ev )
    {
      if ( ! vFnEnforceValidation() )
      {
        alert( 'Please fix any field validation errors before submitting the form as complete.' )
        return false
      }
      return $(this).data('click').call( this, ev )
    }
  })
})
</script>
<?php


	}



	protected function outputRegexValidation( $instrument )
	{
		if ( ! $this->getSystemSetting( 'enable-regex' ) )
		{
			return;
		}

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
  var vFields = JSON.parse('<?php echo json_encode($listRegexFields); ?>')
  Object.keys( vFields ).forEach( function( vFieldName )
  {
    var vFieldData = vFields[ vFieldName ]
    if ( vFieldData.type == 'notes' )
    {
      var vFieldObj = $('textarea[name="' + vFieldName + '"]')[0]
    }
    else
    {
      var vFieldObj = $('input[name="' + vFieldName + '"]')[0]
    }
    vFieldObj.onblur = function() { vFuncRegexValidate( this, vFieldData.regex ) }
  })
})
</script>
<?php


		}
	}



	protected function outputSurveyValidationSkip()
	{
		if ( ! $this->getProjectSetting( 'survey-skip-validate' ) )
		{
			return;
		}

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



	public function validateSettings( $settings )
	{
		if ( $settings['no-past-dates'] )
		{
			if ( ( $settings['past-date-event'] == '' && $settings['past-date-field'] != '' ) ||
			     ( $settings['past-date-event'] != '' && $settings['past-date-field'] == '' ) )
			{
				return 'To determine past dates by field, both event and field must be specified.';
			}
			if ( $settings['past-date-event'] == '' && $settings['past-date'] == '' )
			{
				return 'To disallow entry of dates in the past, either an event/field or a fixed ' .
				       'date must be specified to determine past dates.';
			}
			if ( $settings['past-date'] != '' &&
			     ! preg_match( '/^[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/',
			                   $settings['past-date'] ) )
			{
				return 'Invalid date entered.';
			}
		}
		return null;
	}


}

