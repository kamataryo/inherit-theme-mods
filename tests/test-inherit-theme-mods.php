<?php
/**
 * Test cases.
 * *Problems*
 * - It seems to be better to generate theme at the start of test and
 *   to remove them at the end of test, but the destructor seems not to
 *   be available for phpunit.
 * - Thats's why each function test_* has provisioning and cleaning up sections.
 * - In this way, wp_get_themes() still remains old. Something like
 *   'reload()' may be necessary for WordPress to refresh wp_get_themes(),
 *   but I've not found it so far.
 */
class InheritThemeModsTest extends WP_UnitTestCase {
	var $themes;
	function __construct() {
		parent::__construct();

		$this->themes = array(
			array(
				'Theme Name' => 'Parent Theme',
				'Theme URI' => 'http://biwako.io/',
				'Author' => 'KamataRyo',
				'Author URI' => 'https://biwako.io/',
				'Description' => 'Test theme',
				'Version' => '1.0.0',
				'License' => 'GNU General Public License v2 or later',
				'License URI' => 'http://www.gnu.org/licenses/gpl-2.0.html',
				'Text Domain' => 'inherit-theme-mods-parent-theme',
				'Tags' => '',
			),

			array(
				'Template' => 'inherit-theme-mods-parent-theme',
				'Theme Name' => 'Child Theme',
				'Theme URI' => 'http://biwako.io/',
				'Author' => 'KamataRyo',
				'Author URI' => 'https://biwako.io/',
				'Description' => 'Test theme',
				'Version' => '1.0.0',
				'License' => 'GNU General Public License v2 or later',
				'License URI' => 'http://www.gnu.org/licenses/gpl-2.0.html',
				'Text Domain' => 'inherit-theme-mods-child-theme',
				'Tags' => '',
			),
		);

	}
	/**
	 * Test for test helper functions above.
	 */
	function test_theme_generation() {
		// provisioning
		$themes = $this->__generate_themes();

		// assertions
		foreach ( $themes as $theme ) {
			$this->assertArrayHasKey( $theme['Text Domain'], wp_get_themes() );
		}
		// clean up
		$this->__remove_themes();
	}

	function test_class_construction_success() {
		// provisioning
		$themes = $this->__generate_themes();

		$parent_theme_slug = $themes[0]['Text Domain'];
		$child_theme_slug  = $themes[1]['Text Domain'];
		switch_theme( $child_theme_slug );
		$itm = new Inherit_Theme_Mods();

		//assertion
		$this->assertEquals( $parent_theme_slug, $itm->parent_theme_slug );
		$this->assertEquals( $child_theme_slug,  $itm->child_theme_slug  );

		// clean up
		$this->__remove_themes();
	}

	function test_get_theme_mods_of_fails() {
		// provisioning
		$themes = $this->__generate_themes();

		// assertions
		$this->assertFalse( Inherit_Theme_Mods::get_theme_mods_of( 'undefined-theme-slug' ) );

		//clean up.
		$this->__remove_themes();
	}

	function test_get_theme_mods_of_success() {
		// provisioning
		$themes = $this->__generate_themes();

		// select certain theme
		$theme_slug = $themes[0]['Text Domain'];
		switch_theme( $theme_slug );

		// update theme_mod with wordpress native function
		$name = 'new-theme-mod-to-test';
		$value = 'aaa';
		set_theme_mod( $name, $value );

		// test new plugin's function if the update above can be detected.
		$result = Inherit_Theme_Mods::get_theme_mods_of( $theme_slug );
		$this->assertEquals( $value, $result[$name] );

		//clean up
		remove_theme_mod( $name );
		$this->__remove_themes();
	}


	function test_set_theme_mods_of_fails() {
		// do update with new plugin's function
		$result = Inherit_Theme_Mods::set_theme_mods_of(
			'nonsense-theme-name',
			array( 'some name' => 'some value' )
		);

		// however test fails with nonsense theme name not existing.
		$this->assertFalse( $result );
	}

	function test_set_theme_mods_of_success() {
		//provisioning
		$themes = $this->__generate_themes();

		// create value for updating
		$theme_slug = $themes[0]['Text Domain'];
		$name = 'undefined-new-theme-mod-to-test';
		$value = 'aaa';
		$option_value = array( $name => $value );

		// do update with new plugin's function
		Inherit_Theme_Mods::set_theme_mods_of( $theme_slug, $option_value );

		// obtain actual updated values.
		switch_theme( $theme_slug );
		$actual = get_theme_mods();

		// test if mods updated actually
		$this->assertEquals($option_value[$name] ,$actual[$name] );

		//clean up.
		remove_theme_mod( $name );
		$this->__remove_themes();
	}

	function test_merge_theme_mods_of_fails() {
		// do update with new plugin's function
		$result = Inherit_Theme_Mods::merge_theme_mods_of(
			'nonsense-theme-name',
			array( 'some name' => 'some value' )
		);

		// however test fails with nonsense theme name not existing.
		$this->assertFalse( $result );
	}

	function test_merge_theme_mods_of_success() {
		//provisioning
		$themes = $this->__generate_themes();

		// create value for updating
		$name_overwriter = 'name1';
		$value_overwriter = 'val1';
		$options_overwriter = array( $name_overwriter => $value_overwriter );

		//create value to be update
		$name_overwritee = 'name1';
		$value_overwritee = 'overwritten';
		$name_not_overwritee = 'name2';
		$value_not_overwritee = 'not-overwritten';
		$options_overwritee = array(
			$name_overwritee     => $value_overwritee,
			$name_not_overwritee => $value_not_overwritee,
		);

		//set values
		$theme_slug = $themes[0]['Text Domain'];
		switch_theme( $theme_slug );
		set_theme_mod( $name_overwritee, $value_overwritee );
		set_theme_mod( $name_not_overwritee, $value_not_overwritee );

		// do update with new plugin's function
		Inherit_Theme_Mods::merge_theme_mods_of( $theme_slug, $options_overwriter );
		$actual = get_theme_mods();

		// test if overwritten
		$this->assertEquals($value_overwriter ,$actual[$name_overwritee] );
		// test if not overweitten
		$this->assertEquals($value_not_overwritee ,$actual[$name_not_overwritee] );

		//clean up.
		remove_theme_mod( $name_overwritee );
		remove_theme_mod( $name_not_overwritee );
		$this->__remove_themes();
	}



	function test_inherit_fails() {
		// provisioning
		$themes = $this->__generate_themes();

		// change to Template, nothing to be inherited
		$parent_theme_slug = $themes[0]['Text Domain'];
		$child_theme_slug  = $themes[1]['Text Domain'];
		$itm = new Inherit_Theme_Mods( $parent_theme_slug, $child_theme_slug );
		switch_theme( $parent_theme_slug );

		// test if inheritance fails.
		$this->assertFalse( $itm->inherit() );

		//clean up.
		$this->__remove_themes();
	}


	function test_inherit_and_retore_success() {
		//provisioning
		$themes = $this->__generate_themes();

		$parent_theme_slug = $themes[0]['Text Domain'];
		$child_theme_slug  = $themes[1]['Text Domain'];
		switch_theme( $parent_theme_slug );

		//Set up values to inherit from
		$option_name_for_parent = 'undefined-new-theme-mod-to-test';
		$option_value_for_parent = 'aaa';
		set_theme_mod( $option_name_for_parent, $option_value_for_parent );

		//Set up values to be overwrite
		switch_theme( $child_theme_slug );
		$option_name_for_child = 'undefined-new-theme-mod-to-testsdssss';
		$option_value_for_child = 'bbb';
		set_theme_mod( $option_name_for_child, $option_value_for_child );
		$be_storing = get_theme_mods();

		$itm = new Inherit_Theme_Mods( $parent_theme_slug, $child_theme_slug );

		//inherit
		$result = $itm->inherit();
		$actual = get_theme_mods();

		//test if inheritted
		$this->assertTrue( $result );
		$this->assertEquals( $option_value_for_parent, $actual[$option_name_for_parent] );

		// test if stored
		$stored = Inherit_Theme_Mods::get_stored_mods();
		$this->assertEquals( $option_value_for_child, $stored[$option_name_for_child] );

		// restore
		$result = $itm->restore();

		//test if restored
		$this->assertTrue( $result );
		$this->assertEquals( $option_value_for_child, $stored[$option_name_for_child] );

		//clean up.
		remove_theme_mod( $option_name_for_child );
		switch_theme( $parent_theme_slug );
		remove_theme_mod( $option_name_for_parent );
		$this->__remove_themes();
	}

	/**
	 * helper function for this test.
	 * this function generates themes attributes provided by arguments above.
	 */
	function __generate_themes() {
		foreach ( $this->themes as $theme ) {
			$folder = get_template_directory () . '/../' . $theme['Text Domain'];
			$css = $folder . '/style.css';
			$php = $folder . '/index.php';

			$css_content = "/*\n" ;
			foreach ($theme as $key => $value) {
				$css_content .= "$key: $value\n";
			}
			$css_content .= "*/\n" ;

			if ( !file_exists( $folder ) ) {
				mkdir( $folder , 0777, true);
			}
			file_put_contents( $css, $css_content );
			file_put_contents( $php, '<?php' );
		}
		return $this->themes;
	}

	/**
	 * helper function for this test.
	 * this function removes themes by ceratain arguments above.
	 */
	function __remove_themes() {
		foreach ( $this->themes as $theme ) {
			$folder = get_template_directory () . '/../' . $theme['Text Domain'];
			$css = $folder . '/style.css';
			$php = $folder . '/index.php';

			if ( file_exists( $css ) ) {
				unlink( $css );
			}
			if ( file_exists( $php ) ) {
				unlink( $php );
			}
			if ( file_exists( $folder ) ) {
				rmdir( $folder );
			}
		}
		return $this->themes;
	}
}
