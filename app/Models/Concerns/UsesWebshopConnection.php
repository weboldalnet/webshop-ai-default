<?php

namespace Weboldalnet\WebshopAiDefault\Models\Concerns;

use Weboldalnet\WebshopAiDefault\Support\WebshopConnection;

/**
 * A webshop-modell mindig a webshop közös (alapnyelvi) adatbázisát használja, nem a kérés nyelvéét
 * — lásd WebshopConnection. Egy kifejezetten beállított `$connection` (pl. `Model::on()`) elsőbbséget kap.
 */
trait UsesWebshopConnection
{
    public function getConnectionName()
    {
        return $this->connection ?? WebshopConnection::name();
    }
}
