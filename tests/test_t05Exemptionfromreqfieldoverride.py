# Generated by Selenium IDE
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities

class TestT05Exemptionfromreqfieldoverride():
  def setup_method(self, method):
    self.driver = webdriver.Firefox()
    self.vars = {}
  
  def teardown_method(self, method):
    self.driver.quit()
  
  def test_t05Exemptionfromreqfieldoverride(self):
    self.driver.get("http://127.0.0.1/")
    self.driver.find_element(By.LINK_TEXT, "My Projects").click()
    elements = self.driver.find_elements(By.XPATH, "//*[@id=\"table-proj_table\"][contains(.,\'Validation Tweaker Test\')]")
    assert len(elements) > 0
    self.driver.find_element(By.LINK_TEXT, "Validation Tweaker Test").click()
    self.driver.find_element(By.CSS_SELECTOR, "a[href*=\"record_status_dashboard.php\"]").click()
    self.driver.find_element(By.CSS_SELECTOR, "button[onclick*=\"record_home.php\"][onclick*=\"auto=1\"]").click()
    self.driver.find_element(By.CSS_SELECTOR, "#event_grid_table a[href*=\"page=prescreening_survey\"]").click()
    self.driver.execute_script("$(\'#south\').remove();dataEntrySubmit(\'submit-btn-saverecord\')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.find_element(By.CSS_SELECTOR, "#event_grid_table a[href*=\"page=participant_morale_questionnaire\"]").click()
    self.driver.execute_script("window.location=$(\'a[onclick*=\"surveyOpen\"]:not([onclick*=\"logout=1\"])\').attr(\'onclick\').replace(/.*\'http/s,\'http\').replace(/\'.*/s,\'\')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "footer")))
    self.driver.find_element(By.NAME, "submit-btn-saverecord").click()
    elements = self.driver.find_elements(By.XPATH, "//div[contains(@class,\'ui-dialog\')]//a[contains(text(),\'Continue anyway\')]")
    assert len(elements) == 0
    self.driver.execute_script("window.history.back()")
    self.driver.execute_script("window.history.back()")
    self.driver.execute_script("window.history.back()")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.find_element(By.CSS_SELECTOR, "a[href*=\"record_home.php\"][href*=\"&id=\"]").click()
    self.driver.execute_script("$(\'#south\').remove();deleteRecord(getParameterByName(\'id\'), getParameterByName(\'arm\'))")
    self.driver.find_element(By.XPATH, "//button[contains(text(),\'Close\')]").click()
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
  
