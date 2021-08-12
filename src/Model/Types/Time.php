<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Model\Types;

use Swift\Model\Mapping\Field;
use Swift\Model\Query\TableQuery;

/**
 * Class Time
 * @package Swift\Model\Types
 */
class Time implements TypeInterface {

    public const TIME = 'time';

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return 'datetime';
    }

    public function transformToPhpValue( mixed $value ): string {
        return (new \DateTime($value))->format('H:i:s');
    }

    public function transformToDatabaseValue( mixed $value ): string {
        return (new \DateTime($value))->format('H:i:s');
    }

    public function getName(): string {
        return self::TIME;
    }
}