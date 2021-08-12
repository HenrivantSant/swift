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
 * Class Bool
 * @package Swift\Model\Types
 */
class Boolean implements TypeInterface {

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration( Field $field, TableQuery $query ): string {
        return sprintf( 'tinyint(%s)', $field->getLength() ?? 2 );
    }

    public const BOOL = 'bool';

    public function transformToPhpValue( mixed $value ): bool {
        return (bool) $value;
    }

    public function transformToDatabaseValue( mixed $value ): int {
        return (int) $value;
    }

    public function getName(): string {
        return self::BOOL;
    }


}