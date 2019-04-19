<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

/**
 * LiteralContentTypeTest.php
 * skyline-router
 *
 * Created on 2019-04-19 12:03 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Router\Description\MutableRegexActionDescription;
use Skyline\Router\Event\HTTPRequestRouteEvent;
use Skyline\Router\HTTP\LiteralContentTypeRouter;
use Skyline\Router\HTTP\RegexContentTypeRouter;
use Symfony\Component\HttpFoundation\Request;

class LiteralContentTypeTest extends TestCase
{
    public function testLiteral() {
        $router = new LiteralContentTypeRouter([
            'text/html' => self::class . "::html",
            'text/xml' => self::class . "::xml"
        ]);

        $event = new HTTPRequestRouteEvent( Request::create("http://www.tasoft.ch/my/uri") );
        $router->routeEvent("event", $event, NULL);

        $this->assertEquals(self::class, $event->getActionDescription()->getActionControllerClass());
        $this->assertEquals("html", $event->getActionDescription()->getMethodName());

        $event = new HTTPRequestRouteEvent( Request::create("http://www.tasoft.ch/my/uri", 'PUT', [], [], [], [
            'HTTP_ACCEPT' => "text/xml;q=0.5,text/php;q=0.8"
        ]) );
        $router->routeEvent("event", $event, NULL);

        $this->assertEquals(self::class, $event->getActionDescription()->getActionControllerClass());
        $this->assertEquals("xml", $event->getActionDescription()->getMethodName());
    }

    public function testRegex() {
        $router = new RegexContentTypeRouter([
            '%text/(php|html|htm)%i' => self::class . "::html",
            '%text/(js|javascript)%i' => self::class . "::xml_$1"
        ]);

        $event = new HTTPRequestRouteEvent( Request::create("http://www.tasoft.ch/my/uri", 'PUT', [], [], [], [
            'HTTP_ACCEPT' => "text/xml;q=0.5,text/php;q=0.8"
        ]) );

        $router->routeEvent("event", $event, NULL);

        $this->assertEquals(self::class, $event->getActionDescription()->getActionControllerClass());
        $this->assertEquals("html", $event->getActionDescription()->getMethodName());

        $event = new HTTPRequestRouteEvent( Request::create("http://www.tasoft.ch/my/uri", 'PUT', [], [], [], [
            'HTTP_ACCEPT' => "text/xml;q=0.5,text/js;q=0.8"
        ]) );
        $router->routeEvent("event", $event, NULL);

        $this->assertEquals(self::class, $event->getActionDescription()->getActionControllerClass());
        $this->assertEquals("xml_js", $event->getActionDescription()->getMethodName());

        /** @var MutableRegexActionDescription $ad */
        $ad = $event->getActionDescription();
        $this->assertInstanceOf(MutableRegexActionDescription::class, $ad);

        $this->assertEquals([
            "text/js",
            "js"
        ], $ad->getCaptures());
    }
}
