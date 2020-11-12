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
  var vMakeRow = function( tag, desc, position )
  {
    var vRow = $( '<tr>' + $('tr:has(td.nowrap:contains("' + position + '")):eq(0)').html() + '</tr>' )
    vRow.find('td:eq(1)').html( tag )
    vRow.find('td:eq(2)').html( desc )
    vRow.find('button').attr('onclick', vRow.find('button').attr('onclick').replace(position,tag))
    $('tr:has(td.nowrap:contains("' + position + '")):eq(0)').before( vRow )
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
                          '@TODAY' )
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
		$this->outputDateValidation( $instrument );
		$this->outputEnforceValidation( $instrument );
	}



	public function redcap_survey_page_top( $project_id, $record=null, $instrument, $event_id,
	                                        $group_id=null, $survey_hash=null, $response_id=null,
	                                        $repeat_instance=1 )
	{
		$this->outputDateValidation( $instrument );
		$this->outputSurveyValidationSkip();
	}



	protected function outputDateValidation( $instrument )
	{
		$blockFutureDates = $this->getProjectSetting( 'no-future-dates' );
		$blockPastDates = $this->getProjectSetting( 'no-past-dates' );
		$listDateFields = array();
		$listFormFields = \REDCap::getDataDictionary( 'array', false, true, $instrument );

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
				$listDateFields[ $fieldName ] = [ 'type' => $infoField[ self::VTYPE ],
				                                  'len' => $fieldLength,
				                                  'nofuture' => $noFutureDate,
				                                  'notbefore' => '' ];
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
    var vNotBefore = vFieldData.notbefore
    var vNotAfter = ''
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
			if ( $infofield['field_req'] )
			{
				$reqFields .= $reqFields == '' ? '' : ', ';
				$reqFields .= ( $infoField['element_type'] == 'select' ? 'select' : 'input' );
				$reqFields .= '[name="' . $fieldName . '"]:visible';
			}
		}


?>
<script type="text/javascript">
$(function()
{
  $('#valtext_rangesoft2').text('Please check.')
  $('input:visible, select:visible').each( function()
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
        if ( confirm( 'WARNING: Some data is invalid or incomplete.\n\n' +
                      'It is highly recommended that you cancel now and correct any errors.\n\n' +
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


}

