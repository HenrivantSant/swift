<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Controller;


use Psr\Http\Message\RequestInterface;
use Swift\Kernel\Attributes\DI;
use Swift\Router\RouteInterface;

/**
 * Interface ControllerInterface
 * @package Swift\Controller
 */
interface ControllerInterface {

    public function getRoute(): RouteInterface;

    public function setRoute(RouteInterface $route): void;

    public function getRequest(): RequestInterface;

}