<?php
/*
 * This file is part of UPEM API project.
 *
 * Based on https://github.com/Esipe-IR/UPEM-API
 *
 * (c) 2016-2017 Vincent Rasquier <vincent.rsbs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AppBundle\Security;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class CasEnablerAuthenticator
 */
class CasEnablerAuthenticator extends AbstractGuardAuthenticator
{
    protected $serverLoginUrl;
    protected $serverValidationUrl;
    protected $xmlNamespace;
    protected $usernameAttribute;
    protected $queryTicketParameter;
    protected $queryServiceParameter;
    protected $options;

    /**
     * Process configuration
     * @param array $config
     */
    public function __construct($config)
    {
        $this->serverLoginUrl = $config['server_login_url'];
        $this->serverValidationUrl = $config['server_validation_url'];
        $this->xmlNamespace = $config['xml_namespace'];
        $this->usernameAttribute = $config['username_attribute'];
        $this->queryServiceParameter = $config['query_service_parameter'];
        $this->queryTicketParameter = $config['query_ticket_parameter'];
        $this->options = $config['options'];
    }

    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     *
     * @return array|null
     */
    public function getCredentials(Request $request)
    {
        if ($request->get($this->queryTicketParameter)) {
            $url = $this->serverValidationUrl.'?'.$this->queryTicketParameter.'='.$request->get($this->queryTicketParameter).'&'.$this->queryServiceParameter.'='.urlencode($this->removeCasTicket($request->getUri()));

            $client = new Client();
            $response = $client->request('GET', $url, $this->options);

            $string = $response->getBody()->getContents();

            $xml = new \SimpleXMLElement($string, 0, false, $this->xmlNamespace, true);

            if (isset($xml->authenticationSuccess)) {
                return (array) $xml->authenticationSuccess;
            }
        }

        if ($this->options["env"] === "dev") {
            return $this->options["default_user"];
        }

        return null;
    }

    /**
     * Calls the UserProvider providing a valid User
     * @param array $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return mixed
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (isset($credentials[$this->usernameAttribute])) {
            return $userProvider->loadUserByUsername($credentials[$this->usernameAttribute]);
        }

        return null;
    }

    /**
     * Mandatory but not in use in a remote authentication
     * @param $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * Mandatory but not in use in a remote authentication
     * @param Request $request
     * @param TokenInterface $token
     * @param $providerKey
     *
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($request->query->has($this->queryTicketParameter)) {
            return new RedirectResponse($this->removeCasTicket($request->getUri()));
        }

        return null;
    }

    /**
     * Mandatory but not in use in a remote authentication
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, 403);
    }

    /**
     * Called when authentication is needed, redirect to your CAS server authentication form
     *
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->serverLoginUrl.'?'.$this->queryServiceParameter.'='.urlencode($request->getUri()));
    }

    /**
     * Mandatory but not in use in a remote authentication
     *
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Strip the CAS 'ticket' parameter from a uri.
     *
     * @return
     */
    protected function removeCasTicket($uri)
    {
        $parsedUrl = parse_url($uri);

        if (empty($parsedUrl['query'])) {
            return $uri;
        }

        parse_str($parsedUrl['query'], $queryParams);

        if (!isset($queryParams[$this->queryTicketParameter])) {
            return $uri;
        }

        unset($queryParams[$this->queryTicketParameter]);

        if (empty($queryParams)) {
            unset($parsedUrl['query']);
        } else {
            $parsedUrl['query'] = http_build_query($queryParams);
        }

        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'].'://' : '';
        $host     = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port     = isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '';
        $user     = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass     = isset($parsedUrl['pass']) ? ':'.$parsedUrl['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query    = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
