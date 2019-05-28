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

namespace Skyline\Router;


use Skyline\Router\Description\ActionDescriptionInterface;
use Skyline\Router\Description\MutableActionDescription;
use Skyline\Router\Description\MutableActionDescriptionInterface;
use Skyline\Router\Event\RouteEventInterface;
use TASoft\EventManager\EventManagerInterface;

/**
 * The partial router is used to route several information into an action description.
 *
 * It assumes an array with keys and values, where keys are being matched against a string and if matching, the information will be transformed into an action description
 *
 * @package Skyline\Router
 */
abstract class AbstractPartialRouter implements RouterInterface
{
    /** @var iterable */
    private $routerInfo;

    /**
     * AbstractPartialRouter constructor.
     * @param iterable|null $routerInfo
     */
    public function __construct(iterable $routerInfo = NULL)
    {
        $this->routerInfo = $routerInfo;
    }

    /**
     * Returns an iterable with keys to be compared against comparison string.
     *
     * @return iterable
     */
    public function getRouterInfo(): iterable
    {
        return $this->routerInfo;
    }


    /**
     * Route information into an action description.
     * If completly succeeded, return true to leave routing process.
     *
     * @param $information
     * @param MutableActionDescription $actionDescription
     * @return bool
     */
    abstract protected function routePartial($information, MutableActionDescriptionInterface $actionDescription): bool;

    /**
     * Extracts a string from an event into a comparison string.
     *
     * This string is compared against keys from routerInfo
     *
     * @param RouteEventInterface $event
     * @return string|null
     */
    abstract protected function getComparisonString(RouteEventInterface $event): ?string;

    /**
     * This method is called for every key in routerInfo.
     * If any key matches, the routePartial method gets called to route the value of key to an action description.
     * The information is passed to this method as well and can be modified if needed.
     *
     * @param string $comparisonString
     * @param $routerInfoKey
     * @param mixed $information
     * @return bool
     */
    abstract protected function doesStringMatch(string $comparisonString, $routerInfoKey, &$information): bool;


    /**
     * Used by default implementation to specify a default mutable action description class
     * @return string
     */
    protected function getMutableActionDescriptionClass(): string {
        return MutableActionDescription::class;
    }

    /**
     * Called, if the events action description is not mutable.
     *
     * @param ActionDescriptionInterface|null $actionDescription
     * @return ActionDescriptionInterface
     */
    protected function makeMutableActionDescription(?ActionDescriptionInterface $actionDescription): ActionDescriptionInterface {
        $class = $this->getMutableActionDescriptionClass();

        return is_object($actionDescription) ?
            new $class( $actionDescription->getActionControllerClass(), $actionDescription->getMethodName() ) :
            new $class();
    }

    /**
     * @inheritDoc
     */
    public function routeEvent(string $eventName, RouteEventInterface $event, ?EventManagerInterface $eventManager, ...$arguments)
    {
        $string = $this->getComparisonString($event);
        if(!$string)
            return;

        // Verify, that the action description IS mutable!
        $actionDescription = $event->getActionDescription();

        if(!($actionDescription instanceof MutableActionDescriptionInterface)) {
            $ac = $this->makeMutableActionDescription($actionDescription);

            if($actionDescription !== $ac && method_exists($event, 'setActionDescription'))
                $event->setActionDescription($ac);

            $actionDescription = $ac;
        }


        foreach($this->getRouterInfo() as $key => $information) {
            // Check, if comparison string matches
            if($this->doesStringMatch($string, $key, $information)) {
                // If so, route it
                if($this->routePartial($information, $actionDescription)) {
                    // Stop routing process on success
                    $event->stopPropagation();
                    return;
                }
            }
        }
    }
}