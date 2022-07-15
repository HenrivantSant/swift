<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation;


use Swift\DependencyInjection\Attributes\DI;

/**
 * Represents a cookie.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
#[DI( exclude: true, autowire: false )]
class Cookie {

    public const SAMESITE_NONE = 'none';
    public const SAMESITE_LAX = 'lax';
    public const SAMESITE_STRICT = 'strict';
    private static string $reservedCharsList = "=,; \t\r\n\v\f";
    private static array $reservedCharsFrom = [ '=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f" ];
    private static array $reservedCharsTo = [ '%3D', '%2C', '%3B', '%20', '%09', '%0D', '%0A', '%0B', '%0C' ];
    protected string $name;
    protected ?string $value;
    protected ?string $domain;
    protected int $expire;
    protected $path;
    protected ?bool $secure;
    protected bool $httpOnly;
    private bool $raw;
    private $sameSite;
    private bool $secureDefault = false;

    /**
     * @param string $name The name of the cookie
     * @param string|null $value The value of the cookie
     * @param int $expire The time the cookie expires
     * @param string|null $path The path on the server in which the cookie will be available on
     * @param string|null $domain The domain that the cookie is available to
     * @param bool|null $secure Whether the client should send back the cookie only over HTTPS or null to auto-enable this when the request is already using HTTPS
     * @param bool $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
     * @param bool $raw Whether the cookie value should be sent with no url encoding
     * @param string|null $sameSite Whether the cookie will be available for cross-site requests
     *
     */
    public function __construct( string $name, string $value = null, $expire = 0, ?string $path = '/', string $domain = null, bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = 'lax' ) {
        // from PHP source code
        if ( $raw && false !== strpbrk( $name, self::$reservedCharsList ) ) {
            throw new \InvalidArgumentException( sprintf( 'The cookie name "%s" contains invalid characters.', $name ) );
        }

        if ( empty( $name ) ) {
            throw new \InvalidArgumentException( 'The cookie name cannot be empty.' );
        }

        $this->name     = $name;
        $this->value    = $value;
        $this->domain   = $domain;
        $this->expire   = self::expiresTimestamp( $expire );
        $this->path     = empty( $path ) ? '/' : $path;
        $this->secure   = $secure;
        $this->httpOnly = $httpOnly;
        $this->raw      = $raw;
        $this->sameSite = $this->withSameSite( $sameSite )->sameSite;
    }

    /**
     * Creates a cookie copy with SameSite attribute.
     *
     * @param string|null $sameSite
     *
     * @return static
     */
    public function withSameSite( ?string $sameSite ): static {
        if ( '' === $sameSite ) {
            $sameSite = null;
        } elseif ( null !== $sameSite ) {
            $sameSite = strtolower( $sameSite );
        }

        if ( ! \in_array( $sameSite, [ self::SAMESITE_LAX, self::SAMESITE_STRICT, self::SAMESITE_NONE, null ], true ) ) {
            throw new \InvalidArgumentException( 'The "sameSite" parameter value is not valid.' );
        }

        $cookie           = clone $this;
        $cookie->sameSite = $sameSite;

        return $cookie;
    }

    /**
     * Creates cookie from raw header string.
     *
     * @param string $cookie
     * @param bool $decode
     *
     * @return static
     */
    public static function fromString( string $cookie, bool $decode = false ): static {
        $data = [
            'expires'  => 0,
            'path'     => '/',
            'domain'   => null,
            'secure'   => false,
            'httponly' => false,
            'raw'      => ! $decode,
            'samesite' => null,
        ];

        $parts = HeaderUtils::split( $cookie, ';=' );
        $part  = array_shift( $parts );

        $name  = $decode ? urldecode( $part[0] ) : $part[0];
        $value = isset( $part[1] ) ? ( $decode ? urldecode( $part[1] ) : $part[1] ) : null;

        $data            = HeaderUtils::combine( $parts ) + $data;
        $data['expires'] = self::expiresTimestamp( $data['expires'] );

        if ( isset( $data['max-age'] ) && ( $data['max-age'] > 0 || $data['expires'] > time() ) ) {
            $data['expires'] = time() + (int) $data['max-age'];
        }

        return new static( $name, $value, $data['expires'], $data['path'], $data['domain'], $data['secure'], $data['httponly'], $data['raw'], $data['samesite'] );
    }

    /**
     * Converts expires formats to a unix timestamp.
     *
     * @param int|string|\DateTimeInterface $expire
     *
     * @return int
     */
    private static function expiresTimestamp( $expire = 0 ): int {
        // convert expiration time to a Unix timestamp
        if ( $expire instanceof \DateTimeInterface ) {
            $expire = $expire->format( 'U' );
        } elseif ( ! is_numeric( $expire ) ) {
            $expire = strtotime( $expire );

            if ( false === $expire ) {
                throw new \InvalidArgumentException( 'The cookie expiration time is not valid.' );
            }
        }

        return 0 < $expire ? (int) $expire : 0;
    }

    public static function create( string $name, string $value = null, $expire = 0, ?string $path = '/', string $domain = null, bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = self::SAMESITE_LAX ): static {
        return new self( $name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite );
    }

    /**
     * Creates a cookie copy with a new value.
     *
     * @param string|null $value
     *
     * @return static
     */
    public function withValue( ?string $value ): self {
        $cookie        = clone $this;
        $cookie->value = $value;

        return $cookie;
    }

    /**
     * Creates a cookie copy with a new domain that the cookie is available to.
     *
     * @param string|null $domain
     *
     * @return static
     */
    public function withDomain( ?string $domain ): self {
        $cookie         = clone $this;
        $cookie->domain = $domain;

        return $cookie;
    }

    /**
     * Creates a cookie copy with a new time the cookie expires.
     *
     * @param int|string|\DateTimeInterface $expire
     *
     * @return static
     */
    public function withExpires( $expire = 0 ): self {
        $cookie         = clone $this;
        $cookie->expire = self::expiresTimestamp( $expire );

        return $cookie;
    }

    /**
     * Creates a cookie copy with a new path on the server in which the cookie will be available on.
     *
     * @param string $path
     *
     * @return static
     */
    public function withPath( string $path ): self {
        $cookie       = clone $this;
        $cookie->path = '' === $path ? '/' : $path;

        return $cookie;
    }

    /**
     * Creates a cookie copy that only be transmitted over a secure HTTPS connection from the client.
     *
     * @param bool $secure
     *
     * @return static
     */
    public function withSecure( bool $secure = true ): static {
        $cookie         = clone $this;
        $cookie->secure = $secure;

        return $cookie;
    }

    /**
     * Creates a cookie copy that be accessible only through the HTTP protocol.
     *
     * @param bool $httpOnly
     *
     * @return static
     */
    public function withHttpOnly( bool $httpOnly = true ): static {
        $cookie           = clone $this;
        $cookie->httpOnly = $httpOnly;

        return $cookie;
    }

    /**
     * Creates a cookie copy that uses no url encoding.
     *
     * @param bool $raw
     *
     * @return static
     */
    public function withRaw( bool $raw = true ): static {
        if ( $raw && false !== strpbrk( $this->name, self::$reservedCharsList ) ) {
            throw new \InvalidArgumentException( sprintf( 'The cookie name "%s" contains invalid characters.', $this->name ) );
        }

        $cookie      = clone $this;
        $cookie->raw = $raw;

        return $cookie;
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString(): string {
        if ( $this->isRaw() ) {
            $str = $this->getName();
        } else {
            $str = str_replace( self::$reservedCharsFrom, self::$reservedCharsTo, $this->getName() );
        }

        $str .= '=';

        if ( '' === (string) $this->getValue() ) {
            $str .= 'deleted; expires=' . gmdate( 'D, d-M-Y H:i:s T', time() - 31536001 ) . '; Max-Age=0';
        } else {
            $str .= $this->isRaw() ? $this->getValue() : rawurlencode( $this->getValue() );

            if ( 0 !== $this->getExpiresTime() ) {
                $str .= '; expires=' . gmdate( 'D, d-M-Y H:i:s T', $this->getExpiresTime() ) . '; Max-Age=' . $this->getMaxAge();
            }
        }

        if ( $this->getPath() ) {
            $str .= '; path=' . $this->getPath();
        }

        if ( $this->getDomain() ) {
            $str .= '; domain=' . $this->getDomain();
        }

        if ( true === $this->isSecure() ) {
            $str .= '; secure';
        }

        if ( true === $this->isHttpOnly() ) {
            $str .= '; httponly';
        }

        if ( null !== $this->getSameSite() ) {
            $str .= '; samesite=' . $this->getSameSite();
        }

        return $str;
    }

    /**
     * Checks if the cookie value should be sent with no url encoding.
     *
     * @return bool
     */
    public function isRaw(): bool {
        return $this->raw;
    }

    /**
     * Gets the name of the cookie.
     *
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Gets the value of the cookie.
     *
     * @return string|null
     */
    public function getValue(): ?string {
        return $this->value;
    }

    /**
     * Gets the time the cookie expires.
     *
     * @return int
     */
    public function getExpiresTime(): int {
        return $this->expire;
    }

    /**
     * Gets the max-age attribute.
     *
     * @return int
     */
    public function getMaxAge(): int {
        $maxAge = $this->expire - time();

        return 0 >= $maxAge ? 0 : $maxAge;
    }

    /**
     * Gets the path on the server in which the cookie will be available on.
     *
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * Gets the domain that the cookie is available to.
     *
     * @return string|null
     */
    public function getDomain(): ?string {
        return $this->domain;
    }

    /**
     * Checks whether the cookie should only be transmitted over a secure HTTPS connection from the client.
     *
     * @return bool
     */
    public function isSecure(): bool {
        return $this->secure ?? $this->secureDefault;
    }

    /**
     * Checks whether the cookie will be made accessible only through the HTTP protocol.
     *
     * @return bool
     */
    public function isHttpOnly(): bool {
        return $this->httpOnly;
    }

    /**
     * Gets the SameSite attribute.
     *
     * @return string|null
     */
    public function getSameSite(): ?string {
        return $this->sameSite;
    }

    /**
     * Whether this cookie is about to be cleared.
     *
     * @return bool
     */
    public function isCleared(): bool {
        return 0 !== $this->expire && $this->expire < time();
    }

    /**
     * @param bool $default The default value of the "secure" flag when it is set to null
     */
    public function setSecureDefault( bool $default ): void {
        $this->secureDefault = $default;
    }
}
