<?php

namespace App\Helpers;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class TaxHelper
{
    private $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
    }

    private function getCountriesFromCache()
    {
        return $this->cache->get('countries', function (ItemInterface $item) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 day'));
            return $this->loadCountriesFromDatabase();
        });
    }

    private function loadCountriesFromDatabase()
    {
        // Загрузка данных из базы данных и возврат коллекции с кодами стран
        return ['DE', 'FR', 'GR'];
    }
}