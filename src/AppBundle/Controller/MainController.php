<?php

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

class MainController extends Controller
{
    /**
     * @Route("/~vrasquie/core/auth", name="auth")
     */
    public function authAction()
    {
        return $this->render('actions/close.html.twig');
    }

    /**
     * @Route("/~vrasquie/core/connect", name="connect")
     */
    public function connectAction(Request $request)
    {
        return $this->render('actions/connect.html.twig', array(
            "target" => $request->query->get("target")
        ));
    }
    
    /**
     * @Route("/~vrasquie/core/token", name="token")
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
     * @Route("/~vrasquie/core/graphql", name="graphql")
     */
    public function graphqlAction(Request $request)
    {
        $schema = new Schema([
            "query" => $this->get("query.type")
        ]);

        $json = json_decode($request->getContent(), true);

        $query = isset($json["query"]) ? $json["query"] : null;
        $root = array("token" => $request->headers->get("Authorization"));
        $var = isset($json["variables"]) ? $json["variables"] : null;

        return new JsonResponse(GraphQL::execute($schema, $query, $root, null, $var), 200, array(
            "Access-Control-Allow-Origin" => "*",
            "Access-Control-Allow-Headers" => "Content-Type, Authorization",
            "Content-Type" => "application/json;charset=UTF-8"
        ));
    }

    /**
     * @Route("/~vrasquie/core/graphql/explorer", name="graphql_explorer")
     */
    public function graphqlExplorerAction()
    {
        return $this->render("actions/explorer.html.twig", array(
            'graphQLUrl' => $this->generateUrl("graphql"),
            'tokenHeader' => 'Authorization'
        ));
    }
}
