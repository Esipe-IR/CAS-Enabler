<?php

namespace AppBundle\Controller;

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
     * @Route("/graphql", name="graphql")
     */
    public function graphqlAction(Request $request)
    {
        $graphqlService = $this->get("graphql.service");

        $queryType = new ObjectType([
            "name" => "Query",
            "fields" => [
                "calendar" => array(
                    "type" => $graphqlService->getCalendarType(),
                    "resolve" => function() {
                        return true;
                    }
                ),
                "user" => array(
                    "type" => $graphqlService->getUserType(),
                    "resolve" => function($root) {
                        $jwt = $this->get("jwt.service")->decode($root["token"]);

                        if (!$jwt) {
                            throw new \Exception("Invalid token");
                        }

                        return $this->get("user.service")->getUser($jwt->uid)->toArray();
                    }
                )
            ],
        ]);

        $schema = new Schema([
            "query" => $queryType
        ]);

        $json = json_decode($request->getContent(), true);
        $query = isset($json["query"]) ? $json["query"] : null;
        $root = array(
            "token" => $request->headers->get("Authorization")
        );
        $var = isset($json["variables"]) ? $json["variables"] : null;

        return new JsonResponse(GraphQL::execute($schema, $query, $root, null, $var));
    }

    /**
     * @Route("/graphql/explorer", name="graphql_explorer")
     */
    public function graphqlExplorerAction()
    {
        return $this->render("actions/explorer.html.twig", array(
            'graphQLUrl' => $this->generateUrl("graphql"),
            'tokenHeader' => 'Authorization'
        ));
    }
}
