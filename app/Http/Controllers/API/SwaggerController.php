<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="TasteAsia RESTful API",
 *     description="RESTful API Documentation for TasteAsia. Explore our endpoints and discover how to interact with our delicious data!",
 *     @OA\Contact(
 *         email="gabrielaswinta@cosrsivalab.space"
 *     )
 * )
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="TasteAsia API Server"
 * )
 * @OA\Server(
 *           url=L5_SWAGGER_CONST_LOCAL_HOST,
 *           description="Local Development Server"
 *       ),
 * @OA\Server(
 *           url=L5_SWAGGER_CONST_STAGING_HOST,
 *           description="STAGING API Server"
 *       ),
 * @OA\Server(
 *           url=L5_SWAGGER_CONST_PRODUCTION_HOST,
 *           description="PRODUCTION API Server"
 *       ),
 * @OA\SecurityScheme(
 *      securityScheme="Bearer Token",
 *      type="http",
 *      scheme="bearer",
 *      description="Enter token in format 'Bearer {token}'. Token is a 64-character random string."
 *  )
 * @OA\SecurityScheme(
 *      securityScheme="Access Token",
 *      type="apiKey",
 *      in="header",
 *      name="X-Taste-Asia-Access-Token",
 *      description="Sanctum Access Token (a random string)"
 *  )
 * @OA\SecurityScheme(
 *      securityScheme="Refresh Token",
 *      type="apiKey",
 *      in="header",
 *      name="X-Taste-Asia-Refresh-Token",
 *      description="Sanctum Refresh Token (a random string)"
 *  )
 *
 * @OA\Tag(
 *        name="Welcome",
 *        description="Introductory endpoints for the API.",
 *  )
 * @OA\Tag(
 *       name="Registration",
 *       description="Endpoints for creating new user accounts."
 *   )
 * @OA\Tag(
 *       name="Authentication",
 *       description="Endpoints for user login, token management, and authorization."
 *   )
 * @OA\Tag(
 *        name="Customer",
 *        description="Endpoints for user login, token management, and authorization."
 *    )
 * @OA\Tag(
 *         name="Homepage",
 *         description="Endpoints for mobile Homepage."
 *     )
 * @OA\Tag(
 *        name="Wallet",
 *        description="Endpoints for managing user's cash and credit balances and transactions."
 *    )
 * @OA\Tag(
 *        name="Transaction",
 *        description="Endpoints for managing transaction from vending machine."
 *    )
 * @OA\Tag(
 *        name="Product",
 *        description="Endpoints for managing products."
 *    )
 * @OA\Tag(
 *        name="Location",
 *        description="Endpoints for managing location."
 *    )
 */
class SwaggerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/",
     *     tags={"Welcome"},
     *     summary="Welcome to TasteAsia API",
     *     description="A simple greeting message to get you started.",
     *     security={{"Bearer Token" : {}}},
     *     @OA\Response(response="200", description="API Welcome Message")
     * )
     */
    public function welcome()
    {
        return response()->json([
            "message" => "Welcome to the TasteAsia API! Explore our endpoints and discover a world of culinary delights."
        ]);
    }
}
