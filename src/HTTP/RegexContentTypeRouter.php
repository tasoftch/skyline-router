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

namespace Skyline\Router\HTTP;


use Skyline\Router\Event\HTTPRequestRouteEvent;
use Skyline\Router\Event\RouteEventInterface;

class RegexContentTypeRouter extends RegexURIRouter
{
    protected function getComparisonString(RouteEventInterface $event): ?string
    {
        if($event instanceof HTTPRequestRouteEvent) {
            $types = $event->getRequest()->getAcceptableContentTypes();
            return serialize($types);
        }
        return NULL;
    }

    protected function doesStringMatch(string $comparisonString, $routerInfoKey, &$information): bool
    {
        $types = unserialize($comparisonString);
        foreach($types as $type) {
            if(preg_match($routerInfoKey, $type, $matches)) {
                // Pack matches into information to resolve in self::routePartial
                $information = [
                    preg_replace_callback("/\\$(\d+)/i", function($tag) use ($matches) {
                        $idx = $tag[1] * 1;
                        return $matches[$idx] ?? $tag[0];
                    }, $information)
                    , $matches
                ];
                return true;
            }
            return false;
        }

        return false;
    }
}