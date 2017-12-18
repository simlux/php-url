<?php declare(strict_types=1);

namespace Simlux\Curl\Tests\Url;

use PHPUnit\Framework\TestCase;
use Simlux\Url\Exceptions\InvalidUrlException;
use Simlux\Url\Url;

class UrlTest extends TestCase
{
    public function testThatInvalidUrlThrowsExceptionAfterParsingUrl()
    {
        $this->expectException(InvalidUrlException::class);
        $this->expectExceptionMessage('Invalid url: foobar');

        $url = new Url('foobar');

        $this->assertSame(null, $url->getUrl());
    }

    public function testThatUrlWillBeSanitized()
    {
        $url = new Url('https://www.ex�ample.co�m');

        $this->assertSame('https://www.example.com', $url->getUrl());
    }

    public function dataProviderForTestUrlComponents(): array
    {
        return [
            0 => [
                'url'      => 'http://www.example.com',
                'expected' => [
                    Url::COMPONENT_PROTOCOL => Url::PROTOCOL_HTTP,
                ],
            ],
            1 => [
                'url'      => 'https://www.example.com',
                'expected' => [
                    Url::COMPONENT_PROTOCOL => Url::PROTOCOL_HTTPS,
                ],
            ],
            2 => [
                'url'      => 'https://www.example.com',
                'expected' => [
                    Url::COMPONENT_PROTOCOL => Url::PROTOCOL_HTTPS,
                    Url::COMPONENT_HOST     => 'www.example.com',
                ],
            ],
            3 => [
                'url'      => 'https://www.example.com:8080',
                'expected' => [
                    Url::COMPONENT_PROTOCOL => Url::PROTOCOL_HTTPS,
                    Url::COMPONENT_HOST     => 'www.example.com',
                    Url::COMPONENT_PORT     => 8080,
                ],
            ],
            4 => [
                'url'      => 'https://user:pass@www.example.com:8080',
                'expected' => [
                    Url::COMPONENT_PROTOCOL => Url::PROTOCOL_HTTPS,
                    Url::COMPONENT_HOST     => 'www.example.com',
                    Url::COMPONENT_PORT     => 8080,
                    Url::COMPONENT_USER     => 'user',
                    Url::COMPONENT_PASS     => 'pass',
                ],
            ],
            5 => [
                'url'      => 'https://user:pass@www.example.com:8080/path/to/resource',
                'expected' => [
                    Url::COMPONENT_PROTOCOL => Url::PROTOCOL_HTTPS,
                    Url::COMPONENT_HOST     => 'www.example.com',
                    Url::COMPONENT_PORT     => 8080,
                    Url::COMPONENT_USER     => 'user',
                    Url::COMPONENT_PASS     => 'pass',
                    Url::COMPONENT_PATH     => '/path/to/resource',
                ],
            ],
            6 => [
                'url'      => 'https://www.example.com/path/to/resource/index.php?param1=1&param2=2',
                'expected' => [
                    Url::COMPONENT_PROTOCOL => Url::PROTOCOL_HTTPS,
                    Url::COMPONENT_HOST     => 'www.example.com',
                    Url::COMPONENT_PATH     => '/path/to/resource/index.php',
                    Url::COMPONENT_QUERY    => 'param1=1&param2=2',
                ],
            ],
            7 => [
                'url'      => 'https://www.example.com/path/to/resource/index.php?param1=1&param2=2#fragment',
                'expected' => [
                    Url::COMPONENT_PROTOCOL => Url::PROTOCOL_HTTPS,
                    Url::COMPONENT_HOST     => 'www.example.com',
                    Url::COMPONENT_PATH     => '/path/to/resource/index.php',
                    Url::COMPONENT_QUERY    => 'param1=1&param2=2',
                    Url::COMPONENT_FRAGMENT => 'fragment',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestUrlComponents
     *
     * @param string $url
     * @param array  $expected
     */
    public function testUrlComponents(string $url, array $expected)
    {
        $url = new Url($url);

        $this->assertComponent(Url::COMPONENT_PROTOCOL, $expected, [$url, 'getProtocol']);
        $this->assertComponent(Url::COMPONENT_HOST, $expected, [$url, 'getHost']);
        $this->assertComponent(Url::COMPONENT_PORT, $expected, [$url, 'getPort']);
        $this->assertComponent(Url::COMPONENT_USER, $expected, [$url, 'getUser']);
        $this->assertComponent(Url::COMPONENT_PASS, $expected, [$url, 'getPass']);
        $this->assertComponent(Url::COMPONENT_PATH, $expected, [$url, 'getPath']);
        $this->assertComponent(Url::COMPONENT_QUERY, $expected, [$url, 'getQuery']);
        $this->assertComponent(Url::COMPONENT_FRAGMENT, $expected, [$url, 'getFragment']);
    }

    /**
     * @param string $component
     * @param array  $expected
     * @param array  $func
     */
    private function assertComponent(string $component, array $expected, array $func)
    {
        if (isset($expected[ $component ])) {
            $this->assertSame($expected[ $component ], call_user_func($func));
        }
    }

    public function testThatUrlHasParam()
    {
        $url = new Url('http://ww.example.com/?param1=1&param2=2');

        $this->assertTrue($url->hasParam('param1'));
    }

    public function dataProviderForTestParam(): array
    {
        return [
            0 => [
                'url'      => 'http://ww.example.com/resource?param1=1&param2=2',
                'params'   => [
                    'param2' => 'changed',
                    'param3' => '3',
                ],
                'expected' => 'http://ww.example.com/resource?param1=1&param2=changed&param3=3',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestParam
     *
     * @param string $url
     * @param array  $params
     * @param string $expected
     */
    public function testParam(string $url, array $params, string $expected)
    {
        $url = new Url($url);

        foreach ($params as $key => $value) {
            $url->param($key, $value);
        }

        $this->assertSame($expected, $url->getUrl());
    }

}