<?php
declare(strict_types=1);

namespace test;

use App\DAO\SearchResults;
use App\Logic\Downloader;
use Mockery;
use Nette;
use Tester;
use Tester\Assert;
use function _PHPStan_5a70c2d68\Symfony\Component\String\b;

require __DIR__ . "/../vendor/autoload.php";

class GoogleTest extends Tester\TestCase
{
    public function testGoogle(): void
    {
        $url = 'http://testing/serp';
        $downloader = Mockery::mock(Downloader::class);
        $key = Nette\Utils\Random::generate(10);

        $downloader
            ->shouldReceive('download')
            /*->with([
                'url'=>[
                    'scheme' => 'http',
                    'host' => 'testing',
                    'path' => '/serp',
                    'query' => 'engine=google&api_key='.$key.'&q=Coffee'
                ],
                'formparams' => [
                    'engine'=>'google',
                    'api_key'=>$key,
                    'q'=>'Coffee'
                ]
            ])*/
            ->andReturn(file_get_contents('searchCoffee.json'));

        $processGoogleSerp = new \App\Logic\ProcessGoogleSerp(
            $url,
            new Nette\Caching\Storages\DevNullStorage(),
            $key,
            $downloader
        );
        $processGoogleSerp->setQuery('Coffee');

        $actual = Nette\Utils\Json::decode( Nette\Utils\Json::encode($processGoogleSerp->process()));
        $expected = Nette\Utils\Json::decode(file_get_contents('resultCoffee.json'));


        Assert::equal($actual,$expected);

    }
}

(new GoogleTest())->run();