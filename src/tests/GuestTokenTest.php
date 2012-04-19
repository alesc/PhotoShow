<?php
/**
 * This file implements unit tests for GuestToken class
 * 
 * PHP versions 4 and 5
 *
 * LICENSE:
 * 
 * This file is part of PhotoShow.
 *
 * PhotoShow is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PhotoShow is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhotoShow.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Website
 * @package   Photoshow
 * @author    Franck Royer <royer.franck@gmail.com>
 * @copyright 2012 Thibaud Rohmer
 * @license   http://www.gnu.org/licenses/
 * @link      http://github.com/thibaud-rohmer/PhotoShow
 */

/**
 * Unit test Guest Token
 *
 * I used that for some debug. It's incomplete and I guess
 * It would be better to have a proper framework for unit 
 * test on PHP website. Anyway, it does not harm anyone for now
 *
 * @category  Website
 * @package   Photoshow
 * @license   http://www.gnu.org/licenses/
 */

require_once(realpath(dirname(__FILE__)."/TestUnit.php"));
class GuestTokenTest extends TestUnit
{

    /**
     * Test if keys are generated
     * @test
     * @author Franck Royer
     */
    public function test_generate_key()
    {
        $key = GuestToken::generate_key();
        $this->assertGreaterThan(10, strlen($key));
    }

    /**
     * Quick test on the randomness of the keys
     * @test
     * @depends test_generate_key
     */
    public function test_generate_key_random()
    {
        $key1 = GuestToken::generate_key();
        $key2 = GuestToken::generate_key();
        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test the create feature
     * @test
     * @depends test_generate_key
     */
    public function test_create(){
        // From scratch
        self::delete_tokens_file();
        self::login_as_admin();
       
        $folder1 = Settings::$photos_dir."tokenfolder";
        $ret = GuestToken::create($folder1);
        $this->assertTrue($ret);

        $tokens = GuestToken::findAll();
        $this->assertCount(1, $tokens);
        $this->assertArrayHasKey('key', $tokens[0]);
        $this->assertArrayHasKey('path', $tokens[0]);
        $this->assertEquals(File::a2r($folder1), $tokens[0]['path']);
        $this->assertRegexp('/.{10}.*/',$tokens[0]['key']);

        $folder2 = Settings::$photos_dir."tokenfolder2";
        if (file_exists($folder2)){
            rmdir($folder2);
        }
        // we shouldn't create key for non-existing folders
        try {
            $ret = GuestToken::create($folder2);
        } catch(Exception $e) {
            $this->assertCount(1, GuestToken::findAll());

            mkdir($folder2);
            $ret = GuestToken::create($folder2);
            $this->assertTrue($ret);
            $this->assertCount(2, GuestToken::findAll());

            return;
        }
        $this->fail('Token has been creating on an inexisting folder');
    }


    /**
     * Verify toHTML gives an output
     * @test
     * @depends test_create
     */
    public function test_toHTML()
    {
        self::login_as_admin();
        self::create_token();

        $this->expectOutputRegex("/..*/");
        $guest_token = new GuestToken();
        $guest_token->toHTML();
    }

    /**
     * Verify toHTML gives an output when there is not token file
     * @test
     */
    public function test_toHTML_no_tokens_file()
    {
        self::login_as_admin();
        self::delete_tokens_file();

        $this->expectOutputString("");
        $guest_token = new GuestToken();
        $guest_token->toHTML();
    }

}
?>
