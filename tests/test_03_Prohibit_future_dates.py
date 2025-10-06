# Generated from Selenium IDE
# Test name: t03 Prohibit future dates
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait

class Test_03_Prohibit_future_dates:
  def setup_method(self, method):
    self.driver = self.selectedBrowser
    self.vars = {}
  def teardown_method(self, method):
    self.driver.quit()

  def test_03_Prohibit_future_dates(self):
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
    self.driver.execute_script("//SETDESC:Click \"Completion Data\"")
    self.driver.find_element(By.CSS_SELECTOR, "#event_grid_table a[href*=\"page=completion_data\"]").click()
    self.driver.execute_script("futuredate=new Date();futuredate.setTime(futuredate.getTime()+86400000);futuredate=futuredate.toISOString().substring(0,10);window.futuredate=futuredate")
    self.driver.execute_script("//SETDESC:Enter a future date")
    self.driver.execute_script("$('[name=\"date_visit_4\"]').val(futuredate)")
    self.driver.find_element(By.NAME, "date_visit_4").click()
    self.driver.execute_script("//SETDESC:Click outside the field")
    self.driver.find_element(By.CSS_SELECTOR, "#date_visit_4-tr .labelrc").click()
    self.driver.execute_script("//SETDESC:Assert alert displayed")
    self.driver.find_element(By.XPATH, "//div[@aria-describedby='redcapValidationErrorPopup']").send_keys("SAVESCREENSHOT")
    assert len(self.driver.find_elements(By.XPATH, "//div[@aria-describedby='redcapValidationErrorPopup']")) > 0
    self.driver.execute_script("$('[name=\"date_visit_4\"]').val('')")
    self.driver.find_element(By.XPATH, "//div[@aria-describedby='redcapValidationErrorPopup']//button[contains(@class,'close-button')]").click()
    self.driver.execute_script("//SETDESC:Enter a future date into field with @ALLOWFUTURE action tag")
    self.driver.execute_script("$('[name=\"discharge_date_4\"]').val(futuredate)")
    self.driver.find_element(By.NAME, "discharge_date_4").click()
    self.driver.execute_script("//SETDESC:Click outside the field")
    self.driver.find_element(By.CSS_SELECTOR, "#discharge_date_4-tr .labelrc").click()
    self.driver.execute_script("//SAVEDESC:Assert alert not displayed")
    assert len(self.driver.find_elements(By.XPATH, "//div[@aria-describedby='redcapValidationErrorPopup']")) == 0
    self.driver.execute_script("//SETDESC:Click \"Delete data\"")
    self.driver.find_element(By.CSS_SELECTOR, "[name=\"submit-btn-deleteform\"]").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("$('#south').remove();dataEntrySubmit('submit-btn-deleteform')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.execute_script("$('#south').remove();deleteRecord(getParameterByName('id'), getParameterByName('arm'))")
    self.driver.find_element(By.XPATH, "//button[contains(text(),'Close')]").click()
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
