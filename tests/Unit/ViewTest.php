<?php
/**
 * Tests for mt8\BaseItemList\Admin\View.
 *
 * After Phase 3 the view is a thin shell that renders the React mount point;
 * sanitization and field rendering moved to Rest\SettingsController and the
 * React components respectively.
 *
 * @package mt8\BaseItemList\Tests
 */

namespace mt8\BaseItemList\Tests\Unit;

use mt8\BaseItemList\Admin\View;
use mt8\BaseItemList\Tests\TestCase;

class ViewTest extends TestCase {

	public function test_option_page_renders_react_root_container(): void {
		ob_start();
		View::option_page();
		$output = ob_get_clean();

		$this->assertSame( '<div id="bil-admin-root"></div>', $output );
	}
}
