<?php
/**
 * Class SampleTest
 *
 * @package inherit-theme-mods
 */


/**
 * moc themes
 */
$themes = array(
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

/**
 * this function generates themes attributes provided by arguments above.
 */
function __generate_themes ( $themes )
{
    foreach ( $themes as $theme ) {
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
}

/**
 * this function removes themes by ceratain arguments above.
 */
function __remove_themes ( $themes )
{
    foreach ( $themes as $theme ) {
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
}

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
class InheritThemeModsTest extends WP_UnitTestCase
{
    /**
     * Test for test helper functions above.
     */
    function test_theme_generation()
    {
        // provisioning
        global $themes;
        __generate_themes( $themes );
        // assertions
        foreach ( $themes as $theme ) {
            $this->assertArrayHasKey( $theme['Text Domain'], wp_get_themes() );
        }
        // clean up
        __remove_themes( $themes );

    }

    function test_get_theme_mods_of_fails()
    {
        // provisioning
        global $themes;
        __generate_themes( $themes );

        // assertions
		$result = inherit_theme_mods_get_theme_mods_of( 'undefined-theme-slug' );
		$this->assertFalse( $result );

        //clean up.
        __remove_themes( $themes );
    }

	function test_get_theme_mods_of_success()
    {
        // provisioning
        global $themes;
        __generate_themes( $themes );

        // select certain theme
		$slug = $themes[0]['Text Domain'];
		switch_theme( $slug );

        // update theme_mod with wordpress native function
		$name = 'new-theme-mod-to-test';
		$value = 'aaa';
		set_theme_mod( $name, $value );

        // test new plugin's function if the update above can be detected.
		$result = inherit_theme_mods_get_theme_mods_of( $slug );
		$this->assertEquals( $value, $result[$name] );

        //clean up
		remove_theme_mod( $name );
        __remove_themes( $themes );
    }


	function test_set_theme_mods_of_fails()
    {
        //provisioning
        global $themes;
        __generate_themes( $themes );

        // create value for updating
		$name = 'undefined-new-theme-mod-to-test';
		$value = 'aaa';
		$option_value = array( $name => $value );

        // do update with new plugin's function
        $slug = 'undefined-unknown-theme-name';
		$result = inherit_theme_mods_set_theme_mods_of( $slug, $option_value );

        // however test fails with nonsense theme name not existing.
		$this->assertFalse( $result );

        //clean up.
        __remove_themes( $themes );
    }

    function test_set_theme_mods_of_success()
    {
        //provisioning
        global $themes;
        __generate_themes( $themes );

        // create value for updating
		$slug = $themes[0]['Text Domain'];
		$name = 'undefined-new-theme-mod-to-test';
		$value = 'aaa';
		$option_value = array( $name => $value );

        // do update with new plugin's function
		inherit_theme_mods_set_theme_mods_of( $slug, $option_value );

        // obtain actual updated values.
		switch_theme( $slug );
		$actual = get_theme_mods();

        // test if mods updated actually
		$this->assertEquals($option_value[$name] ,$actual[$name] );

        //clean up.
		remove_theme_mod( $name );
        __remove_themes( $themes );
    }


    function test_inherit_fails()
    {
        // provisioning
        global $themes;
        __generate_themes( $themes );

        // change to Template, nothing to be inherited
        $slug_parent = $themes[0]['Text Domain'];
        switch_theme( $slug_parent );

        // test if inheritance fails.
        $result = inherit_theme_mods_inherit();
        $this->assertFalse( $result );

        //clean up.
        __remove_themes( $themes );
    }


    function test_inherit_and_retore_success()
    {
        //provisioning
        global $themes;
        __generate_themes( $themes );

        //Set up values to inherit from Template
        $slug_parent = $themes[0]['Text Domain'];
        $slug_child = $themes[1]['Text Domain'];
        switch_theme( $slug_parent );
        $name_parent = 'undefined-new-theme-mod-to-test';
        $value_parent = 'aaa';
        set_theme_mod( $name_parent, $value_parent );

        // prepare for inheritance and store previous value
        switch_theme( $slug_child );
        $name_child = 'undefined-new-theme-mod-to-testsdssss';
        $value_child = 'bbb';
        set_theme_mod( $name_child, $value_child );
        $storing = get_theme_mods();

        //inherit
        $result = inherit_theme_mods_inherit();
        $actual = get_theme_mods();

        //test if inheritted
        $this->assertTrue( $result );
        $this->assertEquals( $value_parent, $actual[$name_parent] );

        // test if stored
        $stored = inherit_theme_mods_get_stored_mods();
        $this->assertEquals( $value_child, $stored[$name_child] );

        // restore
        $result = inherit_theme_mods_restore();

        //test if restored
        $this->assertTrue( $result );
        $this->assertEquals( $value_child, $storing[$name_child] );

        //clean up.
        remove_theme_mod( $name_child );
        switch_theme( $slug_parent );
        remove_theme_mod( $name_parent );
        __remove_themes( $themes );
    }


    // test for helper functions
    function test_build_styleAttr()
    {
        $styles = array(
            'background-color' => '#12345',
            'color' => 'red',
            'padding' => 0
        );
        $styleAttrExpected = 'style="background-color:#12345;color:red;padding:0;"';
        $styleAttrActual = inherit_theme_mods_build_styleAttr( $styles );
        $this->assertEquals( $styleAttrExpected, $styleAttrActual );
    }

    function test_build_styleAttr_xss()
    {
        $styles = array(
            'background-color' => '#12345',
            'color' => 'red',
            'padding' => 0,
            '" ><script>alert(1);</script>' => '',
        );
        $styleAttrActual = inherit_theme_mods_build_styleAttr( $styles );
        $xss_vulnerable_match = preg_match( '/<script>.*/', $styleAttrActual );
        $this->assertEquals( 0, $xss_vulnerable_match );
    }
}
