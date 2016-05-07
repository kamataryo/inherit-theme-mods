<?php

class InheritThemeModsFunctionsTest extends WP_UnitTestCase {

	function test_build_style_attr() {
		$this->assertEquals(
            # expected
            'style="background-color:#12345;color:red;padding:0;"',
            # actual
            ITM_Util::style_attr( array(
    			'background-color' => '#12345',
    			'color' => 'red',
    			'padding' => 0
    		) )
        );
	}

	function test_build_style_attr_xss() {
		$xss_vulnerable_match = preg_match(
            '/<script>.*/',
            # actual
            ITM_Util::style_attr( array(
				'background-color'              => '#12345',
				'color'                         => 'red',
				'padding'                       => 0,
				'" ><script>alert(1);</script>' => '',
				'aaa'                           => '" ><script>alert(1);</script>',
			) )
        );
		$this->assertEquals( 0, $xss_vulnerable_match );
	}
}
