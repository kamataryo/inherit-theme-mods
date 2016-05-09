<?php

class InheritThemeModsUITest extends WP_UnitTestCase
{
    function __construct() {
        parent::__construct();
    }

    static function __mimic_verified_request() {
        $_REQUEST['nonce'] = wp_create_nonce( Inherit_Theme_Mods_UI::NONCE_ACTION );
    }
    static function __mimic_invalid_request() {
        $_REQUEST['nonce'] = '';
    }
    static function __prepare_user( $username, $pass, $caps ) {
        $id = wp_create_user( $username, $pass, "$username@localhost" );
        $user = new WP_User( $id );

        if ( is_string( $caps ) ) {
            $caps = array( $caps );
        }

        foreach ( $caps as $cap ) {
            $user->add_cap( $cap ) ;
        }
        return $id;
    }
    static function __abort_user( $id ) {
        return wp_delete_user( $id );
    }


    function test_construction_success() {

        $this->markTestIncomplete( 'Checks of admin script, style, and ajax are enqueued and regitered.' );

        $this->assertTrue( wp_script_is( 'jquery', 'enqueued' ) );
        $this->assertTrue( wp_script_is( 'itm_script', 'enqueued' ) );
        $this->assertTrue( wp_style_is( 'font-awesome', 'enqueued') );
        $this->assertTrue( wp_style_is( 'itm_style', 'enqueued' ) );

    }

    function test_nonce_verification() {

        self::__mimic_invalid_request();
        //without nonce token
        $this->assertFalse( Inherit_Theme_Mods_UI::verify_nonce() );

        self::__mimic_verified_request();
        //  it refers $_REQUEST implicitly
        $this->assertNotFalse( Inherit_Theme_Mods_UI::verify_nonce() );
        // direct with argument
        $this->assertNotFalse( Inherit_Theme_Mods_UI::verify_nonce( $_REQUEST['nonce'] ) );
    }
}
