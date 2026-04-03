<?php

declare(strict_types=1);

namespace test;

use App\Libs\Downloader;
use Mockery;
use Nette;
use Tester;
use Tester\Assert;

require __DIR__ . "/../vendor/autoload.php";

class GoogleTest extends Tester\TestCase
{
    public function testGoogle(): void
    {
        $url = 'http://testing/serp';
        $query = 'Coffee';

        $downloader = Mockery::mock(Downloader::class);
        $key = Nette\Utils\Random::generate();

        $googleResult = file_get_contents('searchCoffee.json');
        if ($googleResult === false) {
            throw new \Exception("Could not read searchCoffee.json");
        }

        $downloader
            ->shouldReceive('download')
            ->with(
                $url,
                [
                    'engine' => 'google',
                    'api_key' => $key,
                    'q' => $query,
                ],
                []
            )
            ->andReturn($googleResult);

        $processGoogleSerp = new \App\Logic\ProcessGoogleSerp(
            $url,
            new Nette\Caching\Storages\DevNullStorage(),
            $key,
            $downloader
        );
        $processGoogleSerp->setQuery($query);

        $ourResult = file_get_contents('resultCoffee.json');
        if ($ourResult === false) {
            throw new \Exception("Could not read resultCoffee.json");
        }

        $actual = Nette\Utils\Json::decode(Nette\Utils\Json::encode($processGoogleSerp->process()));
        $expected = Nette\Utils\Json::decode($ourResult);


        Assert::equal($actual, $expected);

    }
}

(new GoogleTest())->run();
