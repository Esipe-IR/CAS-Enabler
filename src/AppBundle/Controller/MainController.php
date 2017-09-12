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
namespace AppBundle\Controller;

use AppBundle\GraphQL\CalendarType;
use AppBundle\GraphQL\QueryType;
use AppBundle\GraphQL\UserType;
use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MainController
 */
class MainController extends Controller
{
    /**
     * @Route("/~vrasquie/api/auth", name="auth")
     *
     * @return Response
     */
    public function authAction()
    {
        return $this->render("actions/close.html.twig");
    }

    /**
     * @Route("/~vrasquie/api/connect", name="connect")
     *
     * @return Response
     */
    public function connectAction(Request $request)
    {
        return $this->render("actions/connect.html.twig", [
            "target" => $request->query->get("target"),
        ]);
    }

    /**
     * @Route("/~vrasquie/api/token", name="token")
     *
     * @return Response
     */
    public function tokenAction()
    {
        $type = "rcv::token";
        $responseService = $this->get("response.service");
        $casUser = $this->getUser();

        if (!$casUser) {
            return $responseService->sendError($type, 1, false);
        }

        $jwtService = $this->get("jwt.service");
        $token = $jwtService->generate($casUser->getUsername());

        if (!$token) {
            return $responseService->sendError($type, 2, false);
        }

        return $responseService->sendSuccess($type, $token, false);
    }

    /**
     * @Route("/~vrasquie/api/graphql", name="graphql")
     *
     * @return JsonResponse
     */
    public function graphqlAction(Request $request)
    {
        $schema = new Schema([
            "query" => $this->get("query.type"),
        ]);

        $json = json_decode($request->getContent(), true);

        $query = isset($json["query"]) ? $json["query"] : null;
        $root = ["token" => $request->headers->get("Authorization")];
        $var = isset($json["variables"]) ? $json["variables"] : null;

        return new JsonResponse(GraphQL::execute($schema, $query, $root, null, $var), 200, [
            "Access-Control-Allow-Origin" => "*",
            "Access-Control-Allow-Headers" => "Content-Type, Authorization",
            "Content-Type" => "application/json;charset=UTF-8",
        ]);
    }

    /**
     * @Route("/~vrasquie/api/graphql/explorer", name="graphql_explorer")
     *
     * @return Response
     */
    public function graphqlExplorerAction()
    {
        return $this->render("actions/explorer.html.twig", [
            "graphQLUrl" => $this->generateUrl("graphql"),
            "tokenHeader" => "Authorization",
        ]);
    }
}
