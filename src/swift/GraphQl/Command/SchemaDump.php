<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\GraphQl\Command;

use GraphQL\Utils\SchemaPrinter;
use Swift\Console\Command\Command;
use Swift\GraphQl\Schema;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SchemaDump
 * @package Swift\GraphQl\Command
 */
final class SchemaDump extends Command {

    /**
     * the name of the command (the part after "bin/console")
     * @var string $defaultName
     */
    protected static $defaultName = 'graphql:schema:dump';


    /**
     * GetClientCommand constructor.
     *
     * @param Schema $schema
     */
    public function __construct(
        private Schema $schema,
    ) {
        parent::__construct();
    }

    /**
     * Method to set command configuration
     */
    protected function configure() {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Dump graphql schema in type language')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Dump graphql schema in type language')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $io->writeln('Writing schema...');
        file_put_contents(INCLUDE_DIR . '/etc/schema.graphql', SchemaPrinter::doPrint($this->schema->getSchema()));
        $io->success(sprintf('Wrote schema to %s', INCLUDE_DIR . '/etc/schema.graphql'));

        return 0;
    }

}