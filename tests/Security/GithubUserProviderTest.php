<?php

namespace App\Tests\Security;

use App\Entity\User;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use App\Security\GithubUserProvider;
use Psr\Http\Message\StreamInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObjet;

class GithubUserProviderTest extends TestCase
{
    private MockObjet | Client | null $client;
    private MockObjet | SerializerInterface | null $serializer;
    private MockObjet | ResponseInterface | null $response;
    private MockObjet | StreamInterface | null $streamInterface;
    
    public function setup(): void
    {
        $this->client = $this
            ->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->serializer = $this
            ->getMockBuilder('JMS\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->response = $this
            ->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();

        $this->streamInterface = $this
            ->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();

    }
    
    public function testLoadUserByUsernameReturningAUser(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamInterface);

        $this->streamInterface
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('foo');
        
        $userData = [
            'login' => 'login',
            'name' => 'name',
            'email' => 'email',
            'avatar_url' => 'avatar',
            'html_url' => 'html'
        ];

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn($userData);

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

        $user = $githubUserProvider->loadUserByUsername('accessToken');

        $expectedUser = new User(
            $userData['login'],
            $userData['name'],
            $userData['email'],
            $userData['avatar_url'],
            $userData['html_url']
        );

        $this->assertEquals($expectedUser, $user);
        $this->assertEquals('App\Entity\User', get_class($user));
    }

    public function testLoadUserByUsernameThrowingException(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamInterface);

        $this->streamInterface
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('foo');

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn([]);

        $this->expectException('LogicException');

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $githubUserProvider->loadUserByUsername('accesToken');
    }

    public function tearDown(): void
    {
        $this->client = null;
        $this->serializer = null;
        $this->response = null;
        $this->streamInterface = null;
    }
}