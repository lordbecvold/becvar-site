<?php

namespace App\Command;

use App\Service\Manager\AuthManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RegenerateAuthTokensCommand
 * 
 * Command to regenerate all users' authentication tokens in the database.
 * 
 * @package App\Command
 */
#[AsCommand(name: 'auth:tokens:regenerate', description: 'Regenerate all users tokens in database')]
class RegenerateAuthTokensCommand extends Command
{
    /**
     * @var AuthManager
     * Instance of the AuthManager for handling authentication-related functionality.
     */
    private AuthManager $authManager;

    /**
     * RegenerateAuthTokensCommand constructor.
     *
     * @param AuthManager $authManager The AuthManager service responsible for token regeneration.
     */
    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
        parent::__construct();
    }

    /**
     * Executes the command to regenerate all users' authentication tokens.
     *
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @return int The exit code of the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // regenerate all tokens and get state
        $regenerate_state = $this->authManager->regenerateUsersTokens();

        // check if regeneration is success
        if ($regenerate_state['status']) {
            $io->success('All tokens is regenerated');
        } else {
            $io->error('Token regeneration error: '.$regenerate_state['message']);
        }
            
        return Command::SUCCESS;
    }
}
