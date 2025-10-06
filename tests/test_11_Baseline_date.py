# Generated from Selenium IDE
# Test name: t11 Baseline date
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait

class Test_11_Baseline_date:
  def setup_method(self, method):
    self.driver = self.selectedBrowser
    self.vars = {}
  def teardown_method(self, method):
    self.driver.quit()

  def test_11_Baseline_date(self):
    self.driver.get("http://127.0.0.1/")
    self.driver.find_element(By.LINK_TEXT, "My Projects").click()
    assert len(self.driver.find_elements(By.XPATH, "//*[@id=\"table-proj_table\"][contains(.,'Validation Tweaker Test')]")) > 0
    self.driver.find_element(By.LINK_TEXT, "Validation Tweaker Test").click()
    self.driver.find_element(By.CSS_SELECTOR, "a[href*=\"record_status_dashboard.php\"]").click()
    self.driver.find_element(By.CSS_SELECTOR, "button[onclick*=\"record_home.php\"][onclick*=\"auto=1\"]").click()
    self.driver.execute_script("//SETDESC:Click \"Pre-Screening Survey\"")
    self.driver.find_element(By.CSS_SELECTOR, "#event_grid_table a[href*=\"page=prescreening_survey\"]").click()
    self.driver.find_element(By.CSS_SELECTOR, "button[onclick*=\"setToday\"]").click()
    self.driver.execute_script("//SETDESC:Click \"Save & Stay\"")
    self.driver.find_element(By.CSS_SELECTOR, "[id=\"submit-btn-savecontinue\"]").send_keys("SAVESCREENSHOT")
    self.driver.execute_script("$('#south').remove();dataEntrySubmit('submit-btn-savecontinue')")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.execute_script("//SETDESC:Assert recede baseline date option displayed")
    self.driver.find_element(By.CSS_SELECTOR, "a[title=\"Recede baseline date\"]").send_keys("SAVESCREENSHOT")
    assert len(self.driver.find_elements(By.CSS_SELECTOR, "a[title=\"Recede baseline date\"]")) > 0
    self.driver.execute_script("sessionStorage.setItem('test-dateprerecede',$('[name=\"dob\"]').val())")
    self.driver.execute_script("//SETDESC:Click \"Recede baseline date\"")
    self.driver.find_element(By.CSS_SELECTOR, "a[title=\"Recede baseline date\"]").click()
    self.driver.find_element(By.XPATH, "//div[contains(@class,'ui-dialog')]//button[contains(text(),'Okay')]").click()
    time.sleep(2)
    self.driver.execute_script("$('#south').remove();window.location.reload()")
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.ID, "south")))
    self.driver.execute_script("if(sessionStorage.getItem('test-dateprerecede')!=$('[name=\"dob\"]').val())$('body').attr('data-recede','1')")
    self.driver.execute_script("//SAVEDESC:Reload the page")
    self.driver.execute_script("//SETDESC:Assert date has changed")
    self.driver.find_element(By.CSS_SELECTOR, "[name=\"dob\"]").send_keys("SAVESCREENSHOT")
    assert len(self.driver.find_elements(By.CSS_SELECTOR, "body[data-recede]")) > 0
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
