<?php

declare(strict_types=1);

namespace Drupal\Tests\farm_setup\FunctionalJavascript;

use Drupal\Tests\farm_test\FunctionalJavascript\FarmWebDriverTestBase;

/**
 * Tests installing modules via the setup wizard modules form.
 *
 * @group farm
 *
 * @see \Drupal\farm_setup\Plugin\SetupForm\SetupModulesForm
 */
class SetupModulesFormTest extends FarmWebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_setup',
    'farm_ui_dashboard',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Set the initial setup wizard block plugin state to show the modules form.
    \Drupal::service('farm_setup.wizard')->setBlockPluginId('modules');

    // Login a user with access to the dashboard, setup wizard, and module
    // installation setup form.
    $user = $this->createUser([
      'access farm dashboard',
      'access farm setup wizard',
      'install farm modules',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Tests the setup wizard modules form.
   */
  public function testModulesSetupForm() {

    // Request the dashboard.
    $this->drupalGet('<front>');

    // Confirm that checkboxes are unchecked, and modules are not installed.
    $this->assertCheckboxState('plant', FALSE, FALSE);
    $this->assertCheckboxState('land', FALSE, FALSE);
    $this->assertCheckboxState('activity', FALSE, FALSE);
    $this->assertModuleInstalled('farm_plant', FALSE);
    $this->assertModuleInstalled('farm_land', FALSE);
    $this->assertModuleInstalled('farm_land_types', FALSE);
    $this->assertModuleInstalled('farm_activity', FALSE);

    // Check the plant checkbox and confirm that a summary message is displayed.
    $this->getSession()->getPage()->checkField('plant');
    $this->assertTrue($this->assertSession()->waitForText('The following modules will be installed: Plant asset'));

    // Submit the form.
    $this->getSession()->getPage()->pressButton('Save and continue');

    // Wait for the batch process to complete.
    $this->assertTrue($this->assertSession()->waitForText('Step 3: Resources', 30000));

    // Rebuild the container.
    $this->rebuildContainer();

    // Reset state so that we can see the modules form and reload the dashboard.
    \Drupal::service('farm_setup.wizard')->setBlockPluginId('modules');
    $this->drupalGet('<front>');

    // Confirm that the modules form is visible, the plant checkbox is checked,
    // and the farm_plant module is installed.
    $this->assertSession()->pageTextContains('Step 2: Install modules');
    $this->assertCheckboxState('plant', TRUE, TRUE);
    $this->assertModuleInstalled('farm_plant', TRUE);
  }

  /**
   * Helper function to assert the state of checkboxes.
   *
   * @param string $field_name
   *   The checkbox input field name.
   * @param bool $checked
   *   Boolean if the checkbox should be checked. Defaults to FALSE.
   * @param bool $disabled
   *   Boolean if the checkbox should be disabled. Defaults to FALSE.
   */
  protected function assertCheckboxState(string $field_name, bool $checked = FALSE, bool $disabled = FALSE) {
    $checkbox = $this->getSession()->getPage()->findField($field_name);
    $this->assertNotEmpty($checkbox);
    $this->assertEquals($checked, $checkbox->isChecked());
    $this->assertEquals($disabled, $checkbox->hasAttribute('disabled'));
  }

  /**
   * Helper function to assert the state of a module.
   *
   * @param string $module
   *   The module name.
   * @param bool $installed
   *   Boolean if the module should be installed. Defaults to TRUE.
   */
  protected function assertModuleInstalled(string $module, bool $installed = TRUE) {
    $this->assertEquals($installed, \Drupal::moduleHandler()->moduleExists($module));
  }

}
