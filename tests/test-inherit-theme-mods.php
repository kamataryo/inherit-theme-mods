<?php

/**
* @helper class to manage mods. provide human-readable alias to access each properties
*/
class Mods {
    public $parent_slug;
	public $first_slug;
    public $parent;
	public $first;
    public $child_slug;
    public $child;
    public $trashed;
    public $all;
    public $actual;
    function __construct( $parent_slug, $parent, $child_slug=false, $child=false, $trashed=false ) {
        $this->parent_slug = $parent_slug;
		$this->first_slug = $parent_slug; // alias
        $this->parent = $parent;
		$this->first = $parent; //alias
        $this->child_slug = $child_slug;
        $this->child = $child;
        $this->trashed = $trashed;
        $this->all = array(
            'parent'  => $parent,
            'child'   => $child,
            'trashed' => $trashed,
        );
    }
    // syntax suger to get initial value
    function assigned() {
        return $this;
    }
    function actual() {
        return new Mods(
            $this->parent_slug,
            Inherit_Theme_Mods::get_theme_mods_of( $this->parent_slug ),
            $this->child_slug,
            Inherit_Theme_Mods::get_theme_mods_of( $this->child_slug ),
            Inherit_Theme_Mods::get_stored_mods()
        );
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
class InheritThemeModsTest extends WP_UnitTestCase {

    /**
    * additional assertionFunction
    */
    private function assertArrayDeepEquals( $expected, $actual ) {
        $this->assertSame( count( $expected ), count( $actual ) );
        foreach ( $actual as $key => $value ) {
            if ( is_array( $expected[$key] ) && is_array( $value ) ) {
                $this->assertArrayDeepEquals( $expected[$key], $value );
            } else {
                $this->assertEquals( $expected[$key], $value );
            }
        }
    }

    /**
    * @before
    */
    public function prepareTestCases() {
        $this->themes =  array(
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

        // files
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

        // variables
        $this->parent_slug = $this->themes[0]['Text Domain'];
        $this->child_slug  = $this->themes[1]['Text Domain'];
        $this->theme_slugs = array(
            $this->parent_slug,
            $this->child_slug,
        );

    }

    /**
    * @after
    */
    public function cleanUpTestCases() {
        //files
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
        // variables
        unset( $this->parent_slug );
        unset( $this->child_slug );
        unset( $this->theme_slugs );
    }

    /**
     * Test for if (at)before works.
     */
    function test_theme_gerated() {
        // assertions
        foreach ( $this->themes as $theme ) {
            $this->assertArrayHasKey( $theme['Text Domain'], wp_get_themes() );
        }
    }

    function test_Mods() {
        $mods = new Mods(
            $this->parent_slug,
            array( 'name1' => 'key1' ),
            $this->child_slug,
            array( 'name2' => 'key2' ),
            array( 'name3' => 'key3' )
        );

        $this->assertArrayDeepEquals(
            array( 'name1' => 'key1' ),
            $mods->parent
        );
        $this->assertArrayDeepEquals(
            array( 'name2' => 'key2' ),
            $mods->child
        );
        $this->assertArrayDeepEquals(
            array( 'name3' => 'key3' ),
            $mods->trashed
        );
        $this->assertArrayDeepEquals(
            array( 'name1' => 'key1' ),
            $mods->assigned()->parent
        );
        $this->assertArrayDeepEquals(
            array( 'name2' => 'key2' ),
            $mods->assigned()->child
        );
        $this->assertArrayDeepEquals(
            array( 'name3' => 'key3' ),
            $mods->assigned()->trashed
        );
    }

    function test_class_construction_success() {

        switch_theme( $this->child_slug );
        $itm = new Inherit_Theme_Mods();

        //assertion
        $this->assertEquals( $this->parent_slug, $itm->parent_theme_slug );
        $this->assertEquals( $this->child_slug,  $itm->child_theme_slug  );

    }

    function test_get_theme_mods_of_fails() {
        $this->assertFalse( Inherit_Theme_Mods::get_theme_mods_of(
			'undefined-theme-slug'
		) );
    }

	function test_set_theme_mods_of_fails() {
        // however test fails with nonsense theme name not existing.
        $this->assertFalse( Inherit_Theme_Mods::set_theme_mods_of(
            'nonsense-theme-name',
            array( 'some name' => 'some value' )
        ) );
    }

	function test_merge_theme_mods_of_fails() {
        // however test fails with nonsense theme name not existing.
        $this->assertFalse( Inherit_Theme_Mods::merge_theme_mods_of(
            'nonsense-theme-name',
            array( 'some name' => 'some value' )
        ) );
    }

    function test_get_theme_mods_of_success() {
        foreach ( $this->theme_slugs as $slug ) {
            // select certain theme
            switch_theme( $slug );
            // update theme_mod with wordpress native function
            $mods = new Mods($slug, array(
				"$slug-key1" => 'val1',
				"$slug-key2" => 'val2',
				"$slug-key3" => 'val3',
			) );

			foreach ($mods->assigned()->first as $name => $value) {
				set_theme_mod( $name, $value );
			}

            // test new plugin's function if the update above can be detected.
			// $mods->actual() calls get_theme_mods_of internally.
            $this->assertArrayDeepEquals(
				$mods->assigned()->first,
				$mods->actual()->first
			);

            //clean up
			foreach ($mods->assigned()->first as $name => $value) {
				remove_theme_mod( $name );
			}
        }
    }

    function test_set_theme_mods_of_success() {

        foreach ( $this->theme_slugs as $slug ) {

			$mods = new Mods( $slug, array(
				"$slug-key-01" => "$slug-value-01",
				"$slug-key-02" => "$slug-value-02",
				"$slug-key-03" => "$slug-value-03",
			) );

            // do update with new plugin's function
            Inherit_Theme_Mods::set_theme_mods_of(
				$slug,
				$mods->assigned()->first
			);

            // test if mods updated actually
			switch_theme( $slug );
            $this->assertArrayDeepEquals( $mods->assigned()->first, get_theme_mods() );

            //clean up.
			foreach ( $mods->assigned()->first as $name => $value ) {
				remove_theme_mod( $name );
			}
        }
    }

    function test_merge_theme_mods_of_success() {

		$mods = new Mods(
			$this->parent_slug,
			array(
				'name1' => 'val1-parent',
				'name2' => 'val2-parent',
			),
			$this->child_slug,
			array(
				'name1' => 'val1-child',
				'name3' => 'val3-child',
			)
		);

		Inherit_theme_mods::set_theme_mods_of(
			$this->parent_slug,
			$mods->assigned()->parent
		);
		Inherit_theme_mods::set_theme_mods_of(
			$this->child_slug,
			$mods->assigned()->child
		);

        // do update with new plugin's function
        Inherit_Theme_Mods::merge_theme_mods_of(
			$this->child_slug, $mods->assigned()->parent
		);

		// test if mods merged actually
		$this->assertArrayDeepEquals(
			array(
				'name1' => 'val1-parent',
				'name2' => 'val2-parent',
				'name3' => 'val3-child',
			),
			$mods->actual()->child
		);

        switch_theme( $this->parent_slug );
        foreach ($mods->actual()->parent as $key => $value) {
            remove_theme_mod( $key );
        }
        switch_theme( $this->child_slug );
        foreach ($mods->actual()->parent as $key => $value) {
            remove_theme_mod( $key );
        }
    }

    function test_inherit_fails() {
        switch_theme( $this->parent_slug );
        $this->assertFalse( (new Inherit_Theme_Mods() )->inherit() );
    }

    function test_overwrite_fails() {
        switch_theme( $this->parent_slug );
        $this->assertFalse( (new Inherit_Theme_Mods() )->overwrite() );
    }

    function test_store_fails() {
        switch_theme( $this->parent_slug );
        $this->assertFalse( (new Inherit_Theme_Mods() )->store() );
    }

    function test_restore_fails() {
        switch_theme( $this->parent_slug );
        $this->assertFalse( (new Inherit_Theme_Mods() )->restore() );
    }

    function test_store_success() {

        $mods = new Mods(
            $this->parent_slug,
            array(
                'name1' => 'val1-parent',
                'name2' => 'val2-parent',
            ),
            $this->child_slug,
            array(
                'name1' => 'val1-child',
                'name3' => 'val3-child',
            )
        );

        Inherit_theme_mods::set_theme_mods_of(
            $this->parent_slug,
            $mods->assigned()->parent
        );
        Inherit_theme_mods::set_theme_mods_of(
            $this->child_slug,
            $mods->assigned()->child
        );

        switch_theme( $this->child_slug );

        $this->assertTrue( ( new Inherit_Theme_Mods() )->store() );
        $this->assertArrayDeepEquals(
            $mods->assigned()->child,
            $mods->actual()->trashed
        );
    }

    function test_overwrite_and_restore_success() {

		$mods = new Mods(
			$this->parent_slug,
			array(
				'name1' => 'val1-parent',
				'name2' => 'val2-parent',
			),
			$this->child_slug,
			array(
				'name1' => 'val1-child',
				'name3' => 'val3-child',
			)
		);

		Inherit_theme_mods::set_theme_mods_of(
			$this->parent_slug,
			$mods->assigned()->parent
		);
		Inherit_theme_mods::set_theme_mods_of(
			$this->child_slug,
			$mods->assigned()->child
		);

        switch_theme( $this->child_slug );

        $itm = new Inherit_Theme_Mods();

        //test if inheritted
        $this->assertTrue( $itm->overwrite() );
        $this->assertArrayDeepEquals(
            array(
                'name1' => 'val1-parent',
                'name2' => 'val2-parent',
            ),
            $mods->actual()->child
        );

        // test if automatically stored
        $this->assertArrayDeepEquals(
            $mods->assigned()->child,
            $mods->actual()->trashed
        );

        //test if restored
        $this->assertTrue( $itm->restore() );
        $this->assertArrayDeepEquals(
            $mods->assigned()->child,
            $mods->actual()->child
        );

        //clean up.
        switch_theme( $this->parent_slug );
        foreach ($mods->actual()->parent as $key => $value) {
            remove_theme_mod( $key );
        }
        switch_theme( $this->child_slug );
        foreach ($mods->actual()->parent as $key => $value) {
            remove_theme_mod( $key );
        }
    }

    function test_inherit_and_restore_success() {

		$mods = new Mods(
			$this->parent_slug,
			array(
				'name1' => 'val1-parent',
				'name2' => 'val2-parent',
			),
			$this->child_slug,
			array(
				'name1' => 'val1-child',
				'name3' => 'val3-child',
			)
		);

		Inherit_theme_mods::set_theme_mods_of(
			$this->parent_slug,
			$mods->assigned()->parent
		);
		Inherit_theme_mods::set_theme_mods_of(
			$this->child_slug,
			$mods->assigned()->child
		);

        switch_theme( $this->child_slug );

        $itm = new Inherit_Theme_Mods();

        //test if inheritted
        $this->assertTrue( $itm->inherit() );
        $this->assertArrayDeepEquals(
            array(
                'name1' => 'val1-parent',
                'name2' => 'val2-parent',
                'name3' => 'val3-child',
            ),
            $mods->actual()->child
        );

        // test if automatically stored
        $this->assertArrayDeepEquals(
            $mods->assigned()->child,
            $mods->actual()->trashed
        );

        //test if restored
        $this->assertTrue( $itm->restore() );
        $this->assertArrayDeepEquals(
            $mods->assigned()->child,
            $mods->actual()->child
        );

        //clean up.
        switch_theme( $this->parent_slug );
        foreach ($mods->actual()->parent as $key => $value) {
            remove_theme_mod( $key );
        }
        switch_theme( $this->child_slug );
        foreach ($mods->actual()->parent as $key => $value) {
            remove_theme_mod( $key );
        }
    }
}
