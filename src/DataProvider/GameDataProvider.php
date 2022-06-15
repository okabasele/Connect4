<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\DenormalizedIdentifiersAwareItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Game;

class GameDataProvider implements ContextAwareCollectionDataProviderInterface,DenormalizedIdentifiersAwareItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $collectionDataProvider;
    private $itemDataProvider;

    public function __construct(CollectionDataProviderInterface $collectionDataProvider, ItemDataProviderInterface $itemDataProvider)
    {
        $this->collectionDataProvider = $collectionDataProvider;
        $this->itemDataProvider = $itemDataProvider;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === Game::class;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []) : iterable
    {
        return $this->collectionDataProvider->getCollection($resourceClass, $operationName, $context);
    }
    
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []) :?object
    {
        return $this->itemDataProvider->getItem($resourceClass, $id, $operationName, $context);
    }
}
