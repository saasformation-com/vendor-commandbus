<?php

namespace SaaSFormation\Vendor\CommandBus;

use Psr\Container\ContainerInterface;
use StraTDeS\VO\Single\Id;
use StraTDeS\VO\Single\UUIDV1;

class CommandBusManager
{
    private ContainerInterface $container;
    private array $commands = [];

    private function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function createEmpty(ContainerInterface $container): static
    {
        return new static($container);
    }

    public function addPairsFromConfig(string $path): static
    {
        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = array();
        foreach ($iterator as $info) {
            if(!in_array($info->getFilename(), ['.', '..'])) {
                $files[] = $info->getPathname();
            }
        }

        foreach($files as $file) {
            $json = json_decode(file_get_contents($file), true);
            foreach($json["pairs"] as $pair) {
                $this->commands[$pair["command"]] = $pair;
            }
        }

        return $this;
    }

    /**
     * @throws InvalidCommandException
     */
    public function handleAsync(Command $command): Id
    {
        $commandPair = $this->commands[$command::class];

        if($commandPair["validators"]) {
            foreach($commandPair["validators"] as $validator) {
                /** @var Validator $validatorService */
                $validatorService = $this->container->get($validator);
                if(!$validatorService->validate($command)) {
                    throw new InvalidCommandException();
                }
            }
        }

        $service = $this->container->get($commandPair["handler"]);
        $service->handle($command);

        return UUIDV1::generate();
    }
}
