# Connect4
Un puissance 4 codé en PHP

## Pour lancer le projet :
Mettre à jour les packages
```console
composer install
```

Créer la base de données
```console
symfony console doctrine:database:create
```

Créer et lancer l'image docker de Mercure
```console
docker-compose up -d
```

Lancer le serveur Symfony
```console
symfony server:start --no-tls -d
```