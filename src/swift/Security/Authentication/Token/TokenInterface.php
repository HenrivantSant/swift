<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use stdClass;
use Swift\Security\User\UserInterface;

/**
 * Interface TokenInterface
 * @package Swift\Security\Authentication\Token
 */
interface TokenInterface {

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface;

    /**
     * Check whether token has not expired yet
     *
     * @return bool
     */
    public function hasNotExpired(): bool;

    /**
     * Returns moment the token will expire
     *
     * @return \DateTimeInterface
     */
    public function expiresAt(): \DateTimeInterface;

    /**
     * Check whether token is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Set is authenticated
     *
     * @param bool $isAuthenticated
     */
    public function setIsAuthenticated( bool $isAuthenticated ): void;

    /**
     * Return all token data
     *
     * @return stdClass
     */
    public function getData(): stdClass;

}