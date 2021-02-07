<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authentication\Token;

use Swift\Kernel\Attributes\Autowire;
use Swift\Kernel\Attributes\DI;
use Swift\Security\Authentication\DiTags;
use Swift\Security\Authentication\Passport\PassportInterface;
use Swift\Security\Authentication\Passport\UserInterface;

/**
 * Interface TokenProviderInterface
 * @package Swift\Security\Authentication\Token
 */
#[DI(tags: [DiTags::SECURITY_TOKEN_PROVIDER]), Autowire]
interface TokenProviderInterface {

    /**
     * Get token by user or passport
     *
     * @param UserInterface|PassportInterface $user
     *
     * @return TokenInterface
     */
    public function getTokenByUser( UserInterface|PassportInterface $user ): TokenInterface;

}