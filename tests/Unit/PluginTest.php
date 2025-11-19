<?php
/**
 * Basic smoke tests for the core plugin bootstrap.
 *
 * @package MenuGhost\Tests
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit;

use MenuGhost\Plugin;

/**
 * Ensures the primary plugin class can be instantiated.
 */
class PluginTest extends AbstractUnitTestcase {
	public function test_plugin_instance_can_bootstrap(): void {
		$instance = Plugin::instance();
		$this->assertInstanceOf( Plugin::class, $instance );
	}
}
