<?php

namespace App\Factory;

use App\Entity\Scooter;
use App\Repository\ScooterRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Scooter>
 *
 * @method        Scooter|Proxy                     create(array|callable $attributes = [])
 * @method static Scooter|Proxy                     createOne(array $attributes = [])
 * @method static Scooter|Proxy                     find(object|array|mixed $criteria)
 * @method static Scooter|Proxy                     findOrCreate(array $attributes)
 * @method static Scooter|Proxy                     first(string $sortedField = 'id')
 * @method static Scooter|Proxy                     last(string $sortedField = 'id')
 * @method static Scooter|Proxy                     random(array $attributes = [])
 * @method static Scooter|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ScooterRepository|RepositoryProxy repository()
 * @method static Scooter[]|Proxy[]                 all()
 * @method static Scooter[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Scooter[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Scooter[]|Proxy[]                 findBy(array $attributes)
 * @method static Scooter[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Scooter[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class ScooterFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'location' => null, // TODO add POINT type manually
            'status' => self::faker()->randomNumber(),
            'id' => null, // TODO add GUID type manually
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Scooter $scooter): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Scooter::class;
    }
}
