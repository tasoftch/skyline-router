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


use Skyline\Router\AbstractPartialAssignmentRouter;
use Skyline\Router\Event\HTTPRequestRouteEvent;
use Skyline\Router\Event\RouteEventInterface;

class LiteralURIRouter extends AbstractPartialAssignmentRouter
{
    /** @var int Removes leading URI slashes => /admin/file.html gets admin/file.html */
    const OPT_STRIP_SLASH = 1 << 0;

    /** @var int Ignores query => /admin/file.html?q=search+me gets /admin/file.html */
    const OPT_IGNORE_QUERY = 1 << 1;

    /** @var int Ignores query fragment => /admin/file.html?q=search+me#top gets /admin/file.html?q=search+me */
    const OPT_IGNORE_FRAGMENT = 1 << 2;

    /** @var int Compares case sensitive */
    const OPT_CASE_SENSITIVE_COMPARE = 1<<3;

    /** @var int  */
    private $options = 0;

    /**
     * AbstractLiteralURIRouter constructor.
     * @param iterable|NULL $routerInfo
     * @param int $options
     */
    public function __construct(iterable $routerInfo = NULL, int $options = 15)
    {
        parent::__construct($routerInfo);
        $this->options = $options;
    }

    /**
     * @return int
     */
    public function getOptions(): int
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    protected function getComparisonString(RouteEventInterface $event): ?string
    {
        if($event instanceof HTTPRequestRouteEvent) {
            $URI = $event->getRequest()->getRequestUri();

            $opts = $this->getOptions();

            if(($opts & self::OPT_STRIP_SLASH) && $URI[0] == '/')
                $URI = substr($URI, 1);

            if($opts & self::OPT_IGNORE_QUERY) {
                $URI = explode("?", $URI, 2)[0];
            }

            if($opts & self::OPT_IGNORE_FRAGMENT) {
                $URI = explode("#", $URI, 2)[0];
            }
            return $URI ?: "/";
        }
        return NULL;
    }

    /**
     * @inheritDoc
     */
    protected function doesStringMatch(string $comparisonString, $routerInfoKey, &$information): bool
    {
        return $this->getOptions() & self::OPT_CASE_SENSITIVE_COMPARE ? strcmp($comparisonString, $routerInfoKey) == 0 : strcasecmp($comparisonString, $routerInfoKey) == 0;
    }
}