<?php

// src/DataPersister

namespace App\DataPersister;

use App\Entity\Player;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;

class PlayerDataPersister implements ContextAwareDataPersisterInterface
{

    private $_decoratedDataPersister;

    public function __construct(
        DataPersisterInterface $decoratedDataPersister
    ) {
        $this->_decoratedDataPersister = $decoratedDataPersister;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Player;
    }

    /**
     * @param Player $data
     */
    public function persist($data, array $context = []) : void
    {
        $this->_decoratedDataPersister->persist($data);
    }

    public function remove($data, array $context = [])
    {
        $this->_decoratedDataPersister->remove($data);
    }
}