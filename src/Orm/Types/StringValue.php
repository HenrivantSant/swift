<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Orm\Types;


final class StringValue implements TypeInterface {
    
    public const STRING = 'string';
    
    public function transformToPhpValue( mixed $value ): string {
        return (string) $value;
    }
    
    public function transformToDatabaseValue( mixed $value ): string {
        return (string) $value;
    }
    
    public function getName(): string {
        return self::STRING;
    }
    
    public function getDatabaseType( \Swift\Orm\Mapping\Definition\Field $field ): string {
        return sprintf( 'string(%s)', $field->getLength() ?? 255 );
    }
    
}