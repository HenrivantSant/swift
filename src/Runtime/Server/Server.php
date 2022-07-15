<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <hello@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Runtime\Server;

use Swift\Configuration\ConfigurationInterface;
use Swift\Configuration\Utils;
use Swift\DependencyInjection\ContainerInterface;
use Swift\Events\EventDispatcherInterface;
use Swift\HttpFoundation\Response;
use Swift\HttpFoundation\ServerRequest;
use Swift\DependencyInjection\Attributes\Autowire;
use Swift\Kernel\Event\KernelOnBeforeShutdown;
use Swift\Runtime\RuntimeDiTags;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Application
 * @package Swift\Runtime
 */
#[Autowire]
final class Server extends \Symfony\Component\Console\Application implements ServerInterface {
    
    /** @var \Swift\Runtime\Cli\AbstractRuntimeCommand[] $commands */
    private readonly array $commands;
    private bool $running = false;
    
    /**
     * Application constructor.
     *
     * @param \Swift\Events\EventDispatcherInterface      $eventDispatcher
     * @param \Swift\Configuration\ConfigurationInterface $configuration
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ConfigurationInterface $configuration,
    ) {
        parent::__construct( '<fg=green;options=bold>SWIFT SERVER 🚀</>' );
        $this->setAutoExit( false );
    }
    
    /**
     * @param InputInterface|null  $input
     * @param OutputInterface|null $output
     *
     * @throws \Exception
     */
    public function run( InputInterface $input = null, OutputInterface $output = null ): int {
        if ( $this->running ) {
            throw new \RuntimeException( sprintf( '%s is already running', self::class ) );
        }
        
        $this->running = true;
        
        $this->registerCommands();
        
        $result = parent::run();
        
        $this->shutdown();
        
        return $result;
    }
    
    public function finalize(): void {
        $this->shutdown();
    }
    
    /**
     * Shut down application after outputting response
     *
     */
    private function shutdown(): void {
        $this->eventDispatcher->dispatch( event: new KernelOnBeforeShutdown( request: new ServerRequest(), response: new Response() ) );
        
        exit();
    }
    
    public function getDefaultInputDefinition(): InputDefinition {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption( new InputOption( '--track-time', '-t', InputOption::VALUE_NONE, 'Track and report execution time of command' ) );
        
        return $definition;
    }
    
    /**
     * Method to register commands
     *
     * @throws \Exception
     */
    private function registerCommands(): void {
        if ( empty( $this->commands ) ) {
            return;
        }
        
        $this->addCommands( $this->commands );
    }
    
    public function getHelp(): string {
        return PHP_EOL . $this->getLogo() . PHP_EOL . PHP_EOL . parent::getHelp();
    }
    
    private function getLogo(): string {
        return "<fg=green>
 ________  ___       __   ___  ________ _________   
|\   ____\|\  \     |\  \|\  \|\  _____\\___   ___\ 
\ \  \___|\ \  \    \ \  \ \  \ \  \__/\|___ \  \_| 
 \ \_____  \ \  \  __\ \  \ \  \ \   __\    \ \  \  
  \|____|\  \ \  \|\__\_\  \ \  \ \  \_|     \ \  \ 
    ____\_\  \ \____________\ \__\ \__\       \ \__\
   |\_________\|____________|\|__|\|__|        \|__|
   \|_________|                                     
                                                                  
                </>";
    }
    
    public function isDebug(): bool {
        return Utils::isDebug( $this->configuration );
    }
    
    #[Autowire]
    public function setCommands( #[Autowire( tag: RuntimeDiTags::COMMAND )] ?iterable $commands ): void {
        if ( ! $commands ) {
            $this->commands = [];
            
            return;
        }
        
        $this->commands = iterator_to_array( $commands, false );
    }
    
}