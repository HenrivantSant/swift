<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\HttpFoundation\Session\Storage\Handler;

/**
 * Memcached based session storage handler based on the Memcached class
 * provided by the PHP memcached extension.
 *
 * @see https://php.net/memcached
 *
 * @author Drak <drak@zikula.org>
 */
class MemcachedSessionHandler extends AbstractSessionHandler {

    private \Memcached $memcached;

    /**
     * @var int Time to live in seconds
     */
    private int $ttl;

    /**
     * @var string Key prefix for shared environments
     */
    private $prefix;

    /**
     * Constructor.
     *
     * List of available options:
     *  * prefix: The prefix to use for the memcached keys in order to avoid collision
     *  * expiretime: The time to live in seconds.
     *
     * @param \Memcached $memcached
     * @param array $options
     */
    public function __construct( \Memcached $memcached, array $options = [] ) {
        $this->memcached = $memcached;

        if ( $diff = array_diff( array_keys( $options ), [ 'prefix', 'expiretime' ] ) ) {
            throw new \InvalidArgumentException( sprintf( 'The following options are not supported "%s".', implode( ', ', $diff ) ) );
        }

        $this->ttl    = isset( $options['expiretime'] ) ? (int) $options['expiretime'] : 86400;
        $this->prefix = $options['prefix'] ?? 'sf2s';
    }

    /**
     * @return bool
     */
    public function close(): bool {
        return $this->memcached->quit();
    }

    /**
     * @return bool
     */
    public function updateTimestamp( $sessionId, $data ) {
        $this->memcached->touch( $this->prefix . $sessionId, time() + $this->ttl );

        return true;
    }

    /**
     * @return bool
     */
    public function gc( $maxlifetime ) {
        // not required here because memcached will auto expire the records anyhow.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead( string $sessionId ): string {
        return $this->memcached->get( $this->prefix . $sessionId ) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite( string $sessionId, string $data ): bool {
        return $this->memcached->set( $this->prefix . $sessionId, $data, time() + $this->ttl );
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy( string $sessionId ): bool {
        $result = $this->memcached->delete( $this->prefix . $sessionId );

        return $result || \Memcached::RES_NOTFOUND == $this->memcached->getResultCode();
    }

    /**
     * Return a Memcached instance.
     *
     * @return \Memcached
     */
    protected function getMemcached(): \Memcached {
        return $this->memcached;
    }
}
