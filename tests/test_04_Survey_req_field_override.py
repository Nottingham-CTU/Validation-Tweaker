# Generated from Selenium IDE
# Test name: t04 Survey req field override
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait

class Test_04_Survey_req_field_override:
  def setup_method(self, method):
    self.driver = self.selectedBrowser
    self.vars = {}
  def teardown_method(self, method):
    self.driver.quit()

  def test_04_Survey_req_field_override(self):
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
    self.driver.find_element(By.ID, "SurveyActionDropDown").click()
    self.driver.execute_script("//SETDESC:Click \"Open survey\"")
    self.driver.find_element(By.CSS_SELECTOR, "[id=\"surveyoption-openSurvey\"]:not([onclick*=\"logout=1\"])").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("window.location=$('a[onclick*=\"surveyOpen\"]:not([onclick*=\"logout=1\"])').attr('onclick').replace(/.*'http/s,'http').replace(/'.*/s,'')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "footer")))
    self.driver.find_element(By.NAME, "submit-btn-saverecord").click()
    self.driver.execute_script("//SETDESC:Assert option to continue survey is present")
    self.driver.find_element(By.XPATH, "//div[contains(@class,'ui-dialog')]//a[contains(text(),'More save options')]").send_keys("SAVESCREENSHOT")
    assert len(self.driver.find_elements(By.XPATH, "//div[contains(@class,'ui-dialog')]//a[contains(text(),'More save options')]")) > 0
    self.driver.find_element(By.XPATH, "//div[contains(@class,'ui-dialog')]//a[contains(text(),'More save options')]").click()
    self.driver.find_element(By.XPATH, "//div[contains(@class,'ui-dialog')]//button[contains(text(),'Mark Survey as Complete')]").click()
    self.driver.execute_script("//SAVEDESC:Assert survey submitted")
    assert len(self.driver.find_elements(By.ID, "surveyacknowledgment")) > 0
    self.driver.execute_script("//SAVEDESC:Go back to form")
    self.driver.execute_script("window.history.back()")
    self.driver.execute_script("window.history.back()")
    self.driver.execute_script("window.history.back()")
    self.driver.execute_script("window.history.back()")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.find_element(By.CSS_SELECTOR, "a[href*=\"record_home.php\"][href*=\"&id=\"]").click()
    self.driver.execute_script("$('#south').remove()")
    self.driver.find_element(By.ID, "recordActionDropdownTrigger").click()
    self.driver.find_element(By.CSS_SELECTOR, "[onclick*=\"deleteRecord\"]").click()
    self.driver.find_element(By.CSS_SELECTOR, ".ok-button").click()
    self.driver.find_element(By.XPATH, "//button[contains(text(),'Close')]").click()
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
