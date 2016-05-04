<?php
/**
 * Class SampleTest
 *
 * @package
 */


/**
 * they are test-helpers.
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
 */
class InheritThemeModsTest extends WP_UnitTestCase
{
    //test of test-helper functions
    function test_theme_generation()
    {
        //provisioning
        global $themes;
        __generate_themes( $themes );

        foreach ( $themes as $theme ) {
            $this->assertArrayHasKey( $theme['Text Domain'], wp_get_themes() );
        }

        // clean up, but wp_get_themes still remains old.
        __remove_themes( $themes );

    }

    function test_get_theme_mods_of()
    {
        //provisioning
        global $themes;
        __generate_themes( $themes );

        //obtain expected and actual
        $slug0 = $themes[0]['Text Domain'];
        $slug1 = $themes[1]['Text Domain'];
        $name = 'theKey';
        $value = 'theValue';
        switch_theme( $slug0 );
        set_theme_mod( $name, $value );
        $expected = array( $name => $value );
        switch_theme( $slug1 );
        $actual = inherit_theme_mods_get_theme_mods_of( $slug0 );

        $this->assertEquals( $expected[$name], $actual[$name] );

        //clean up.
        switch_theme( $slug0 );
        remove_theme_mod( $name );
        __remove_themes( $themes );
    }



    function test_set_theme_mods_of()
    {
        //provisioning
        global $themes;
        __generate_themes( $themes );

        //obtain expected and actual
        $slug0 = $themes[0]['Text Domain'];
        $slug1 = $themes[1]['Text Domain'];
        $name = 'theKey';
        $value = 'theValue';
        switch_theme( $slug0 );
        $expected = array( $name => $value );
        inherit_theme_mods_set_theme_mods_of($slug1, $expected );
        switch_theme( $slug1 );
        $actual = get_theme_mods();

        $this->assertEquals( $expected[$name], $actual[$name] );

        //clean up.
        switch_theme( $slug0 );
        remove_theme_mod( $name );
        __remove_themes( $themes );
    }
}
