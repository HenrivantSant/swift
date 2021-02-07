<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Security\Authorization\Strategy;


use Swift\Security\Authentication\Token\TokenInterface;
use Swift\Security\Authorization\Voter\VoterInterface;

/**
 * Class DecisionStrategyAffirmative
 * @package Swift\Security\Authorization\Strategy
 */
class DecisionStrategyAffirmative implements DecisionStrategyInterface {

    /**
     * Grants access if any voter returns an affirmative response.
     *
     * @inheritDoc
     */
    public function decide( array $voters, TokenInterface $token, mixed $subject, array $attributes ): bool {
        foreach ( $voters as $voter ) {
            $result = $voter->vote($token, $subject, $attributes);

            if (VoterInterface::ACCESS_GRANTED === $result) {
                return true;
            }
        }

        return false;
    }
}