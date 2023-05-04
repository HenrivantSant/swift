<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;



final class Boolean implements TypeInterface {
    
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
    
    public function getDatabaseType( \Swift\Orm\Mapping\Definition\Field $field ): string {
        return 'boolean';
    }


}