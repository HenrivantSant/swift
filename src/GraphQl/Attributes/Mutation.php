<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Attributes;

use Attribute;
use JetBrains\PhpStorm\Deprecated;
use Swift\DependencyInjection\Attributes\DI;
use Swift\Security\Authorization\AuthorizationType;

/**
 * Class Mutation
 * @package Swift\GraphQl\Attributes
 */
#[Attribute(Attribute::TARGET_METHOD), DI(exclude: true)]
#[Deprecated]
#[\AllowDynamicProperties]
class Mutation {

    /**
     * Mutation constructor.
     *
     * @param string|null $name
     * @param string|null $type
     * @param string|null $generator
     * @param array $generatorArguments
     * @param string|null $description
     */
    public function __construct(
        public string|null $name = null,
        public string|null $type = null,
        public string|null $generator = null,
        public array $generatorArguments = [],
        public string|null $description = null,
        public array $authTypes = [],
        public array $isGranted = [],
    ) {
    }

    /**
     * @return string|null
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getGenerator(): ?string {
        return $this->generator;
    }

    /**
     * @return array
     */
    public function getGeneratorArguments(): array {
        return $this->generatorArguments;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * @return AuthorizationType[]
     */
    public function getAuthTypes(): array {
        return $this->authTypes;
    }

    /**
     * @return array
     */
    public function getIsGranted(): array {
        return $this->isGranted;
    }


}