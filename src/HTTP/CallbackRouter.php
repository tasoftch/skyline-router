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
use TASoft\EventManager\EventManagerInterface;

/**
 * Class CallbackRouter redirects the routers info to a callback that is responsable to route all stuff into an action description
 *
 * The callback signature is: [bool] function ( Request $request, MutableActionDescriptionInterface $actionDescription )
 *
 * @package Skyline\Router\HTTP
 */
class CallbackRouter extends AbstractPartialAssignmentRouter
{
    /** @var callable */
    private $callback;

    /**
     * CallbackRouter constructor.
     * @param callable $callback
     * @param iterable|NULL $routerInfo
     */
    public function __construct(callable $callback)
    {
        parent::__construct([]);
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @inheritDoc
     */
    protected function getComparisonString(RouteEventInterface $event): ?string
    {
        // won't be called
        return NULL;
    }

    /**
     * @inheritDoc
     */
    protected function doesStringMatch(string $comparisonString, $routerInfoKey, &$information): bool
    {
        // won't be called
        return false;
    }

    /**
     * @inheritDoc
     */
    public function routeEvent(string $eventName, RouteEventInterface $event, ?EventManagerInterface $eventManager, ...$arguments)
    {
        if(is_callable($this->getCallback()) && $event instanceof HTTPRequestRouteEvent) {
            $actionDescription = $event->getActionDescription();
            $class = $this->getMutableActionDescriptionClass();

            if(!($actionDescription instanceof $class)) {
                $ac = $this->makeMutableActionDescription($actionDescription);

                if($actionDescription !== $ac && method_exists($event, 'setActionDescription'))
                    $event->setActionDescription($ac);

                $actionDescription = $ac;
            }

            $request = $event->getRequest();

            if($this->getCallback()($request, $actionDescription)) {
                // Stop routing process on success
                $event->stopPropagation();
                return;
            }
        }
    }
}