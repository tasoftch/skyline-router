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

use Skyline\Router\Description\MutableActionDescriptionInterface;
use Skyline\Router\Description\MutableRegexActionDescription;

/**
 * Routing information keys are PREG patterns that need to match.
 * Captures are replaced in keys value using preg_replace function.
 * @example [
 *      '%/?my/URI-(about|home|news)%i' => '\My\ActionController::deliver$1' // can resolve to \My\ActionController::deliverabout, \My\ActionController::deliverhome or \My\ActionController::delivernews
 * ]
 *
 * @package Skyline\Router\HTTP
 */
class RegexURIRouter extends LiteralURIRouter
{
    /**
     * @inheritDoc
     */
    protected function getMutableActionDescriptionClass(): string
    {
        return MutableRegexActionDescription::class;
    }

    /**
     * @inheritDoc
     */
    protected function doesStringMatch(string $comparisonString, $routerInfoKey, &$information): bool
    {
        if(preg_match($routerInfoKey, $comparisonString, $matches)) {
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

    /**
     * @inheritDoc
     */
    protected function routePartial($information, MutableActionDescriptionInterface $actionDescription): bool
    {
        list($information, $matches) = $information;
        if(method_exists($actionDescription, 'setCaptures'))
            $actionDescription->setCaptures($matches);

        return parent::routePartial($information, $actionDescription);
    }


}