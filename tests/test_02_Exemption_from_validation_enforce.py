# Generated from Selenium IDE
# Test name: t02 Exemption from validation enforce
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait

class Test_02_Exemption_from_validation_enforce:
  def setup_method(self, method):
    self.driver = self.selectedBrowser
    self.vars = {}
  def teardown_method(self, method):
    self.driver.quit()

  def test_02_Exemption_from_validation_enforce(self):
    self.driver.get("http://127.0.0.1/")
    self.driver.find_element(By.LINK_TEXT, "My Projects").click()
    assert len(self.driver.find_elements(By.XPATH, "//*[@id=\"table-proj_table\"][contains(.,'Validation Tweaker Test')]")) > 0
    self.driver.find_element(By.LINK_TEXT, "Validation Tweaker Test").click()
    self.driver.find_element(By.CSS_SELECTOR, "a[href*=\"record_status_dashboard.php\"]").click()
    self.driver.find_element(By.CSS_SELECTOR, "button[onclick*=\"record_home.php\"][onclick*=\"auto=1\"]").click()
    self.driver.execute_script("//SETDESC:Click \"Pre-Screening Survey\"")
    self.driver.find_element(By.CSS_SELECTOR, "#event_grid_table a[href*=\"page=prescreening_survey\"]").click()
    self.driver.execute_script("//SETDESC:Click \"Save & Exit Form\"")
    self.driver.find_element(By.CSS_SELECTOR, "[id=\"submit-btn-saverecord\"]").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("$('#south').remove();dataEntrySubmit('submit-btn-saverecord')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.execute_script("//SETDESC:Click \"Participant Info Survey\"")
    self.driver.find_element(By.CSS_SELECTOR, "#event_grid_table a[href*=\"page=participant_info_survey\"]").click()
    self.driver.find_element(By.NAME, "participant_info_survey_complete").find_element(By.CSS_SELECTOR, "*[value='2']").click()
    self.driver.execute_script("//SETDESC:Click \"Save & Stay\"")
    self.driver.find_element(By.CSS_SELECTOR, "[id=\"submit-btn-savecontinue\"]").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("$('#south').remove();dataEntrySubmit('submit-btn-savecontinue')")
    self.driver.execute_script("//SETDESC:Assert alert displayed")
    self.driver.find_element(By.XPATH, "//div[contains(@class,'simpleDialog') and contains(text(),'submitting the form')]").send_keys("SAVESCREENSHOT")
    assert len(self.driver.find_elements(By.XPATH, "//div[contains(@class,'simpleDialog') and contains(text(),'submitting the form')]")) > 0
    self.driver.find_element(By.XPATH, "//div[contains(@class,'simpleDialog') and contains(text(),'submitting the form')]/..//button[contains(@class,'close-button')]").click()
    self.driver.execute_script("window.dataEntryFormValuesChanged = false")
    self.driver.find_element(By.CSS_SELECTOR, "a[href*=\"record_home.php\"][href*=\"&id=\"]").click()
    self.driver.execute_script("//SETDESC:Click \"Participant Morale Questionnaire\"")
    self.driver.find_element(By.CSS_SELECTOR, "#event_grid_table a[href*=\"page=participant_morale_questionnaire\"]").click()
    self.driver.find_element(By.NAME, "participant_morale_questionnaire_complete").find_element(By.CSS_SELECTOR, "*[value='2']").click()
    self.driver.execute_script("//SETDESC:Click \"Save & Stay\"")
    self.driver.find_element(By.CSS_SELECTOR, "[id=\"submit-btn-savecontinue\"]").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("$('#south').remove();dataEntrySubmit('submit-btn-savecontinue')")
    self.driver.execute_script("//SAVEDESC:Assert form is saved without alert displayed")
    assert len(self.driver.find_elements(By.XPATH, "//div[contains(@class,'simpleDialog') and contains(text(),'submitting the form')]")) == 0
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.find_element(By.XPATH, "//button[contains(text(),'Okay')]").click()
    self.driver.find_element(By.NAME, "pmq1").send_keys("100")
    self.driver.find_element(By.CSS_SELECTOR, "#pmq1-tr .labelrc").click()
    self.driver.find_element(By.XPATH, "//div[@id=\"redcapValidationErrorPopup\"]/..//button[contains(text(),'Close')]").click()
    self.driver.execute_script("//SETDESC:Click \"Save & Stay\"")
    self.driver.find_element(By.CSS_SELECTOR, "[id=\"submit-btn-savecontinue\"]").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("$('#south').remove();dataEntrySubmit('submit-btn-savecontinue')")
    self.driver.execute_script("//SAVEDESC:Assert form is saved without alert displayed")
    assert len(self.driver.find_elements(By.XPATH, "//div[contains(@class,'simpleDialog') and contains(text(),'submitting the form')]")) == 0
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.execute_script("$('[name=\"pmq1\"]').trigger('focus').trigger('blur')")
    time.sleep(1)
    self.driver.execute_script("$('.close-button').trigger('click')")
    self.driver.execute_script("//SETDESC:Click \"Delete data\"")
    self.driver.find_element(By.CSS_SELECTOR, "[name=\"submit-btn-deleteform\"]").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("$('#south').remove();dataEntryFormValuesChanged=false;dataEntrySubmit('submit-btn-deleteform')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.execute_script("//SETDESC:Click \"Participant Morale Questionnaire\"")
    self.driver.find_element(By.CSS_SELECTOR, "#event_grid_table a[href*=\"page=participant_morale_questionnaire\"]").click()
    self.driver.find_element(By.ID, "SurveyActionDropDown").click()
    self.driver.execute_script("//SETDESC:Click \"Open survey\"")
    self.driver.find_element(By.CSS_SELECTOR, "[id=\"surveyoption-openSurvey\"]:not([onclick*=\"logout=1\"])").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("window.location=$('a[onclick*=\"surveyOpen\"]:not([onclick*=\"logout=1\"])').attr('onclick').replace(/.*'http/s,'http').replace(/'.*/s,'')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "footer")))
    self.driver.find_element(By.NAME, "pmq1").click()
    self.driver.find_element(By.NAME, "pmq1").send_keys("100")
    self.driver.find_element(By.ID, "label-pmq1").click()
    time.sleep(0.5)
    self.driver.execute_script("//SETDESC:Assert alert displayed")
    self.driver.find_element(By.XPATH, "//div[contains(@class,'simpleDialog') and contains(text(),'value you provided') and contains(@style,'display')]").send_keys("SAVESCREENSHOT")
    assert len(self.driver.find_elements(By.XPATH, "//div[contains(@class,'simpleDialog') and contains(text(),'value you provided') and contains(@style,'display')]")) > 0
    self.driver.find_element(By.XPATH, "//div[contains(@class,'simpleDialog') and contains(text(),'value you provided')]/..//button[contains(@class,'close-button')]").click()
    self.driver.find_element(By.ID, "label-pmq1").click()
    time.sleep(0.5)
    self.driver.execute_script("//SAVEDESC:Assert no alert")
    assert len(self.driver.find_elements(By.XPATH, "//div[contains(@class,'simpleDialog') and contains(text(),'value you provided') and contains(@style,'display')]")) == 0
    self.driver.execute_script("//SAVEDESC:Go back to previous page")
    self.driver.execute_script("window.history.back()")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.find_element(By.CSS_SELECTOR, "a[href*=\"record_home.php\"][href*=\"&id=\"]").click()
    self.driver.execute_script("$('#south').remove()")
    self.driver.find_element(By.ID, "recordActionDropdownTrigger").click()
    self.driver.find_element(By.CSS_SELECTOR, "[onclick*=\"deleteRecord\"]").click()
    self.driver.find_element(By.CSS_SELECTOR, ".ok-button").click()
    self.driver.find_element(By.XPATH, "//button[contains(text(),'Close')]").click()
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
