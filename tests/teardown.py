# Generated from Selenium IDE
# Test name: teardown
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait

class Test_teardown:
  def setup_method(self, method):
    self.driver = self.selectedBrowser
    self.vars = {}
  def teardown_method(self, method):
    self.driver.quit()

  def test_teardown(self):
    self.driver.get("http://127.0.0.1")
    self.driver.find_element(By.LINK_TEXT, "My Projects").click()
    assert len(self.driver.find_elements(By.XPATH, "//*[@id=\"table-proj_table\"][contains(.,'Validation Tweaker Test')]")) > 0
    self.driver.find_element(By.CSS_SELECTOR, "a[href$=\"ControlCenter/index.php\"]").click()
    self.driver.find_element(By.CSS_SELECTOR, "a[href$=\"ExternalModules/manager/control_center.php\"]").click()
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.CSS_SELECTOR, "tr[data-module=\"validation_tweaker\"] button.external-modules-configure-button")))
    self.driver.find_element(By.CSS_SELECTOR, "tr[data-module=\"validation_tweaker\"] button.external-modules-configure-button").click()
    WebDriverWait(self.driver, 30).until(expected_conditions.presence_of_element_located((By.NAME, "enable-default-calc")))
    self.driver.execute_script("$('[name=\"enable-default-calc\"]').prop('checked',JSON.parse(sessionStorage.getItem('test-savedsetting'))[0]);$('[name=\"enable-regex\"]').prop('checked',JSON.parse(sessionStorage.getItem('test-savedsetting'))[1]);$('[name=\"enable-randomnumber\"]').prop('checked',JSON.parse(sessionStorage.getItem('test-savedsetting'))[2])")
    self.driver.find_element(By.CSS_SELECTOR, "#external-modules-configure-modal .modal-footer .save").click()
    time.sleep(2)
    self.driver.find_element(By.LINK_TEXT, "My Projects").click()
    self.driver.find_element(By.LINK_TEXT, "Validation Tweaker Test").click()
    self.driver.find_element(By.LINK_TEXT, "Other Functionality").click()
    self.driver.find_element(By.CSS_SELECTOR, ".btn-danger").click()
    self.driver.find_element(By.ID, "delete_project_confirm").send_keys("DELETE")
    self.driver.find_element(By.CSS_SELECTOR, ".ui-dialog-buttonset > .ui-button:nth-child(2)").click()
    self.driver.find_element(By.XPATH, "//button[contains(.,'Yes, delete the project')]").click()
    self.driver.execute_script("sessionStorage.removeItem('test-savedsetting')")
    time.sleep(2)
    self.driver.close()
