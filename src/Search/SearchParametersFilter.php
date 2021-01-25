<?php

namespace App\Search;

use App\Geocoder\Coordinates;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\Geocoder;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a way to handle the search parameters.
 */
class SearchParametersFilter
{
    public const CACHE_KEY_PREFIX = 'search_geocoding_city_';

    const PARAMETER_QUERY = 'q';
    const PARAMETER_RADIUS = 'r';
    const PARAMETER_CITY = 'c';
    const PARAMETER_TYPE = 't';
    const PARAMETER_OFFSET = 'offset';
    const PARAMETER_EVENT_CATEGORY = 'ec';
    const PARAMETER_REFERENT_EVENTS = 're';

    public const DEFAULT_QUERY = '';
    public const DEFAULT_TYPE = self::TYPE_COMMITTEES;
    public const DEFAULT_RADIUS = self::RADIUS_50;
    public const DEFAULT_CITY = 'Paris';
    public const DEFAULT_MAX_RESULTS = 30;

    public const TYPE_COMMITTEES = 'committees';
    public const TYPE_EVENTS = 'events';
    public const TYPE_CITIZEN_PROJECTS = 'citizen_projects';
    public const TYPE_CITIZEN_ACTIONS = 'citizen_actions';

    public const TYPES = [
        self::TYPE_COMMITTEES,
        self::TYPE_EVENTS,
        self::TYPE_CITIZEN_PROJECTS,
        self::TYPE_CITIZEN_ACTIONS,
    ];

    public const RADIUS_NONE = -1;
    public const RADIUS_1 = 1;
    public const RADIUS_5 = 5;
    public const RADIUS_10 = 10;
    public const RADIUS_25 = 25;
    public const RADIUS_50 = 50;
    public const RADIUS_100 = 100;
    public const RADIUS_150 = 150;

    public const RADII = [
        self::RADIUS_NONE,
        self::RADIUS_1,
        self::RADIUS_5,
        self::RADIUS_10,
        self::RADIUS_25,
        self::RADIUS_50,
        self::RADIUS_100,
        self::RADIUS_150,
    ];

    private $geocoder;
    private $query;
    private $type;
    private $radius;
    private $city;
    private $offset;
    private $maxResults;
    private $eventCategory;
    private $referentEvents = false;
    private $cache;

    public function __construct(Geocoder $geocoder, AdapterInterface $cache)
    {
        $this->geocoder = $geocoder;
        $this->cache = $cache;
        $this->query = self::DEFAULT_QUERY;
        $this->type = self::DEFAULT_TYPE;
        $this->radius = self::DEFAULT_RADIUS;
        $this->city = self::DEFAULT_CITY;
        $this->offset = 0;
        $this->maxResults = self::DEFAULT_MAX_RESULTS;
    }

    /**
     * @return SearchParametersFilter
     */
    public function handleRequest(Request $request): self
    {
        $this->setQuery((string) $request->query->get(self::PARAMETER_QUERY));
        $this->setType($request->query->get(self::PARAMETER_TYPE, ''));
        $this->setRadius($request->query->getInt(self::PARAMETER_RADIUS, self::DEFAULT_RADIUS));
        $this->setOffset($request->query->getInt(self::PARAMETER_OFFSET));
        $this->setEventCategory($request->query->get(self::PARAMETER_EVENT_CATEGORY, null));
        $this->setReferentEvents($request->query->getBoolean(self::PARAMETER_REFERENT_EVENTS, false));

        if (null !== $city = $request->query->get(self::PARAMETER_CITY)) {
            $this->setCity((string) $city);
        }

        return $this;
    }

    public function setType(string $type): self
    {
        // Will fallback to the default one
        $this->type = \in_array($type, self::TYPES, true) ? $type : self::DEFAULT_TYPE;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setRadius(int $radius): self
    {
        // We fallback to the default one
        $this->radius = \in_array($radius, self::RADII, true) ? $radius : self::DEFAULT_RADIUS;

        return $this;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function setCity(string $city): self
    {
        $this->city = trim($city);

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @throws GeocodingException
     */
    public function getCityCoordinates(): ?Coordinates
    {
        $id = self::CACHE_KEY_PREFIX.md5(strtolower($this->city));
        $item = $this->cache->getItem($id);

        if ($item->isHit() && $item->get() instanceof Coordinates) {
            return $item->get();
        }

        $item->set($coordinates = $this->geocoder->geocode($this->city));
        $this->cache->save($item);

        return $coordinates;
    }

    public function setQuery(string $query): self
    {
        $this->query = trim($query);

        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset < 0 ? 0 : $offset;

        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setMaxResults(int $maxResults): self
    {
        $this->maxResults = $maxResults < 1 ? self::DEFAULT_MAX_RESULTS : $maxResults;

        return $this;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    public function getEventCategory(): ?string
    {
        return $this->eventCategory;
    }

    public function setEventCategory(?string $eventCategory): void
    {
        $this->eventCategory = $eventCategory;
    }

    public function getReferentEvents(): bool
    {
        return $this->referentEvents;
    }

    public function setReferentEvents(bool $referentEvents): void
    {
        $this->referentEvents = $referentEvents;
    }

    public function isTypeCommittees(): bool
    {
        return self::TYPE_COMMITTEES === $this->getType();
    }

    public function isTypeEvents(): bool
    {
        return self::TYPE_EVENTS === $this->getType();
    }

    public function isTypeCitizenProjects(): bool
    {
        return self::TYPE_CITIZEN_PROJECTS === $this->getType();
    }

    public function getRadiusInMeters(): int
    {
        return $this->radius * 1000;
    }
}
