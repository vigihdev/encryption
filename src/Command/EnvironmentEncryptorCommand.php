<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract;


#[AsCommand(
    name: 'env:encrypt',
    description: 'Mengenkripsi satu atau lebih variabel di dalam file .env'
)]
class EnvironmentEncryptorCommand extends Command
{
    public function __construct(
        private readonly EnvironmentEncryptorServiceContract $service
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {

        $this
            ->addArgument(
                'env-path',
                InputArgument::REQUIRED,
                'Path menuju file .env (contoh: .env)',
                null,
                function () {
                    return ['.env'];
                }
            )
            ->addArgument(
                'env-keys',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Satu atau lebih kunci environment yang akan dienkripsi (contoh: APP_KEY DB_PASSWORD)',
                null,
                function () {
                    $dotEnvVars = getenv('SYMFONY_DOTENV_VARS') ? explode(',', getenv('SYMFONY_DOTENV_VARS')) : [];
                    return $dotEnvVars;
                }
            )
            ->setHelp(
                <<<'HELP'
                Command ini memungkinkan Anda untuk mengenkripsi nilai dari variabel environment yang spesifik di dalam file .env.
                
                Contoh penggunaan:
                
                1. Mengenkripsi satu variabel:
                   php bin/console env:encrypt .env APP_KEY
                   
                2. Mengenkripsi beberapa variabel sekaligus:
                   php bin/console env:encrypt .env APP_KEY DB_PASSWORD API_SECRET
                HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $envPath = $input->getArgument('env-path');
        $envKeys = $input->getArgument('env-keys');

        $envFullPath = Path::join(getcwd(), $envPath);

        if (! is_file($envFullPath)) {
            $io->error("File environment '{$envPath}' tidak ditemukan di path: {$envFullPath}");
            return Command::FAILURE;
        }

        $io->info("Mulai proses enkripsi untuk file: {$envPath}...");

        $success = $this->service->encryptEnvironment($envFullPath, $envKeys);

        if ($success) {
            $io->success("Proses enkripsi selesai. Variabel yang dienkripsi telah disimpan kembali ke {$envPath}.");
        } else {
            $io->warning("Tidak ada variabel yang dienkripsi. Kemungkinan semua sudah terenkripsi atau kunci tidak ditemukan.");
        }

        return Command::SUCCESS;
    }
}
