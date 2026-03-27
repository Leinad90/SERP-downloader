<?php
declare(strict_types=1);

namespace App\DAO;

class googleRequestDao
{
    public ?string $engine = 'google';
    public ?string $api_key = null;
    public ?string $q = null;
    public ?string $kgmid = null;
    public ?string $device = null;
    public ?string $location = null;
    public ?string $uule = null;
    public ?string $gl = null;
    public ?string $hl = null;
    public ?string $lr = null;
    public ?string $cr = null;
    public ?string $nfpr = null;
    public ?string $filter = null;
    public ?string $safe = null;
    public ?string $time_period = null;
    public ?string $time_period_min = null;
    public ?string $time_period_max = null;
    public ?int    $page = null;
    public ?string $optimization_strategy = null;
    public ?string $verbatim = null;

    /** Pokud true, použije se Authorization header místo query param api_key */
    public bool $useAuthorizationHeader = false;

    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            } else {
                trigger_error("Key $k does not exist in " . __CLASS__, E_USER_NOTICE);
            }
        }
    }

    public function validate(): void
    {
        if (empty($this->engine) || $this->engine !== 'google') {
            throw new \InvalidArgumentException('engine is required and must be "google".');
        }
        if (empty($this->api_key) && !$this->useAuthorizationHeader) {
            throw new \InvalidArgumentException('api_key is required (or set useAuthorizationHeader and provide api_key for header).');
        }
    }

    public function toArray(): array
    {
        $map = [
            'engine' => $this->engine,
            'api_key' => $this->api_key,
            'q' => $this->q,
            'kgmid' => $this->kgmid,
            'device' => $this->device,
            'location' => $this->location,
            'uule' => $this->uule,
            'gl' => $this->gl,
            'hl' => $this->hl,
            'lr' => $this->lr,
            'cr' => $this->cr,
            'nfpr' => $this->nfpr,
            'filter' => $this->filter,
            'safe' => $this->safe,
            'time_period' => $this->time_period,
            'time_period_min' => $this->time_period_min,
            'time_period_max' => $this->time_period_max,
            'page' => $this->page,
            'optimization_strategy' => $this->optimization_strategy,
            'verbatim' => $this->verbatim,
        ];

        // odstraní null a prázdné stringy
        return array_filter($map, static function ($v) {
            return $v !== null && $v !== '';
        });
    }

    public function toQueryString(): string
    {
        return http_build_query($this->toArray());
    }
}
