<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Voter;


use Swift\Kernel\Attributes\Autowire;
use Swift\Security\Authentication\AuthenticationTypeResolverInterface;
use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\AuthorizationTypesEnum;

/**
 * Class AuthenticatedVoter
 * @package Swift\Security\Authorization\Voter
 */
#[Autowire]
class AuthenticatedVoter implements VoterInterface {

    /**
     * AuthenticatedVoter constructor.
     *
     * @param AuthenticationTypeResolverInterface $authenticationTypeResolver
     */
    public function __construct(
        private AuthenticationTypeResolverInterface $authenticationTypeResolver,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function vote( TokenInterface $token, mixed $subject, array $attributes ): string {
        $vote = VoterInterface::ACCESS_ABSTAIN;

        if (in_array(AuthorizationTypesEnum::PUBLIC_ACCESS, $attributes, true)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        foreach ($attributes as $attribute) {
            // Abstain on non supported attributes
            if (!AuthorizationTypesEnum::isValid($attribute)) {
                continue;
            }

            // Default to no access unless one of the below conditions proves right
            $vote = VoterInterface::ACCESS_DENIED;

            if ((AuthorizationTypesEnum::IS_AUTHENTICATED === $attribute) && $this->authenticationTypeResolver->isAuthenticated()) {
                return VoterInterface::ACCESS_GRANTED;
            }

            if ((AuthorizationTypesEnum::IS_AUTHENTICATED_ANONYMOUSLY === $attribute) && $this->authenticationTypeResolver->isAnonymous()) {
                return VoterInterface::ACCESS_GRANTED;
            }

            if ((AuthorizationTypesEnum::IS_AUTHENTICATED_TOKEN === $attribute) && $this->authenticationTypeResolver->isPreAuthenticated()) {
                return VoterInterface::ACCESS_GRANTED;
            }

            if ((AuthorizationTypesEnum::IS_AUTHENTICATED_DIRECTLY === $attribute) && $this->authenticationTypeResolver->isDirectLogin()) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return $vote;
    }
}