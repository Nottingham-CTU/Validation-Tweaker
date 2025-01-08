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

class TestT10VALIDATEMESSAGEactiontag():
  def setup_method(self, method):
    self.driver = webdriver.Firefox()
    self.vars = {}
  
  def teardown_method(self, method):
    self.driver.quit()
  
  def test_t10VALIDATEMESSAGEactiontag(self):
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
    self.driver.find_element(By.CSS_SELECTOR, "#event_grid_table a[href*=\"page=completion_data\"]").click()
    self.driver.find_element(By.NAME, "tagvalidatelogic").click()
    self.driver.find_element(By.NAME, "tagvalidatelogic").send_keys("1")
    self.driver.find_element(By.NAME, "tagvalidatelogic").click()
    self.driver.find_element(By.CSS_SELECTOR, "#tagvalidatelogic-tr .labelrc").click()
    elements = self.driver.find_elements(By.XPATH, "//div[@aria-describedby=\'redcapValidationErrorPopup\']")
    assert len(elements) > 0
    elements = self.driver.find_elements(By.XPATH, "//div[@aria-describedby=\'redcapValidationErrorPopup\']//div[contains(text(),\'Test validation message\')]")
    assert len(elements) > 0
    self.driver.find_element(By.XPATH, "//div[@aria-describedby=\'redcapValidationErrorPopup\']//button[contains(@class,\'close-button\')]").click()
    self.driver.find_element(By.NAME, "tagvalidatelogic").click()
    self.driver.execute_script("$(\'[name=\"tagvalidatelogic\"]\').val(\'\')")
    self.driver.find_element(By.NAME, "tagvalidatelogic").click()
    self.driver.find_element(By.CSS_SELECTOR, "#tagvalidatelogic-tr .labelrc").click()
    self.driver.execute_script("$(\'#south\').remove();dataEntrySubmit(\'submit-btn-deleteform\')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.execute_script("$(\'#south\').remove();deleteRecord(getParameterByName(\'id\'), getParameterByName(\'arm\'))")
    self.driver.find_element(By.XPATH, "//button[contains(text(),\'Close\')]").click()
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
  
