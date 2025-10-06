# Generated from Selenium IDE
# Test name: t12 UPPERCASE/LOWERCASE action tags
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait

class Test_12_UPPERCASE_LOWERCASE_action_tags:
  def setup_method(self, method):
    self.driver = self.selectedBrowser
    self.vars = {}
  def teardown_method(self, method):
    self.driver.quit()

  def test_12_UPPERCASE_LOWERCASE_action_tags(self):
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
    self.driver.execute_script("//SETDESC:Enter lowercase text into the field with the @UPPERCASE action tag")
    self.driver.find_element(By.NAME, "taguppercase").send_keys("abc")
    self.driver.execute_script("//SETDESC:Click outside of field")
    self.driver.find_element(By.CSS_SELECTOR, "#taguppercase-tr .labelrc").click()
    self.driver.execute_script("//SETDESC:Assert that the text is now uppercase")
    self.driver.find_element(By.CSS_SELECTOR, "[name=\"taguppercase\"]").send_keys("SAVESCREENSHOT")
    assert self.driver.find_element(By.CSS_SELECTOR, "[name=\"taguppercase\"]").get_attribute("value") == "ABC"
    self.driver.execute_script("//SETDESC:Enter uppercase text into the field with the @LOWERCASE action tag")
    self.driver.find_element(By.NAME, "taglowercase").send_keys("ABC")
    self.driver.execute_script("//SETDESC:Click outside of field")
    self.driver.find_element(By.CSS_SELECTOR, "#taglowercase-tr .labelrc").click()
    self.driver.execute_script("//SETDESC:Assert that the text is now lowercase")
    self.driver.find_element(By.CSS_SELECTOR, "[name=\"taglowercase\"]").send_keys("SAVESCREENSHOT")
    assert self.driver.find_element(By.CSS_SELECTOR, "[name=\"taglowercase\"]").get_attribute("value") == "abc"
    self.driver.execute_script("//SETDESC:Click \"Delete data\"")
    self.driver.find_element(By.CSS_SELECTOR, "[name=\"submit-btn-deleteform\"]").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("$('#south').remove();dataEntrySubmit('submit-btn-deleteform')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.execute_script("$('#south').remove()")
    self.driver.find_element(By.ID, "recordActionDropdownTrigger").click()
    self.driver.find_element(By.CSS_SELECTOR, "[onclick*=\"deleteRecord\"]").click()
    self.driver.find_element(By.CSS_SELECTOR, ".ok-button").click()
    self.driver.find_element(By.XPATH, "//button[contains(text(),'Close')]").click()
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
