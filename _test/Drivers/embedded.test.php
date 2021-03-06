<?php
/*
 * Yurii's Gantt Plugin
 *
 * Copyright (C) 2020 Yurii K.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

use dokuwiki\plugin\yuriigantt\src\Driver\Embedded;
use dokuwiki\plugin\yuriigantt\src\Driver\Embedded\Handler;
use dokuwiki\plugin\yuriigantt\src\Driver\Embedded\Renderer;
use dokuwiki\plugin\yuriigantt\src\Driver\Embedded\Lexer;

/**
 * @group plugin_yuriigantt
 * @group plugins
 */
class plugin_yuriigantt_storage_test extends DokuWikiTest
{
    protected $pluginsEnabled = ['yuriigantt'];
//
//    public static function setUpBeforeClass(){
//        parent::setUpBeforeClass();
//        // copy our own config files to the test directory
//        TestUtils::rcopy(dirname(DOKU_CONF), dirname(__FILE__).'/conf');
//    }

    public function testExample()
    {
        $rawPage = file_get_contents(dirname(__DIR__) . '/test_page.txt');

        $handler = new Handler();
        $lexer = new Lexer($handler, Embedded::MODE);
        Embedded::addLexerPattern($lexer, Embedded::MODE);
        $result = $lexer->parse($rawPage);
        $instructions = $handler->calls;

        // check instructions are correct
        $this->assertTrue($result);
        $this->assertEquals('raw', $instructions[0][0]);
        $this->assertEquals('plugin', $instructions[1][0]);
        $this->assertEquals('yuriigantt', $instructions[1][1][0]);
        $this->assertInstanceOf(stdClass::class, $instructions[1][1][1]);
        $this->assertStringStartsWith('~~~~GANTT~~~~', $instructions[1][1][3]);
        $this->assertStringEndsWith('~~~~~~~~~~~', $instructions[1][1][3]);
        $this->assertEquals('raw', $instructions[2][0]);
        $this->assertEquals("\n\n\nzzz\n", $instructions[2][1][0]);

        // check output is same if nothing was updated
        $renderer = new Renderer();
        foreach ($instructions as $instruction) {
            call_user_func_array(array(&$renderer, $instruction[0]), $instruction[1] ? $instruction[1] : array());
        }
        //file_put_contents(__DIR__.'/test_page_output.txt', $renderer->doc);
        $this->assertEquals($renderer->doc, $rawPage);

        // check database
        $database = $handler->getDatabase();
        $this->assertIsObject($database);
        $this->assertEquals($database->pageId, 'asd');
        $this->assertEquals($database->version, '1.0');
        $this->assertEquals($database->dsn, Embedded::DSN);
        $this->assertEquals($database->dsn, Embedded::DSN);
        $this->assertEquals($database->increment->task, 12);
        $this->assertEquals($database->increment->link, 8);
    }
}
