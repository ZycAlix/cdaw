<?php
include_once __ROOT_DIR . '/libs/php-jwt/src/BeforeValidException.php';
include_once __ROOT_DIR . '/libs/php-jwt/src/ExpiredException.php';
include_once __ROOT_DIR . '/libs/php-jwt/src/SignatureInvalidException.php';
include_once __ROOT_DIR . '/libs/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;

class LoginController extends Controller {

   public function __construct($name, $request) {
      parent::__construct($name, $request);
   }

    public function processRequest() {
      if($this->request->getHttpMethod() !== 'POST')
         return Response::errorResponse('{ "message" : "Unsupported endpoint" }' );

      $json = $this->request->jsonContent();

      if(!isset($json->password) || !isset($json->login)) {
        $r = new Response(422,"login and pwd fields are mandatory");
        $r->send();
      }

      $user = User::tryLogin($json->login);
      
      
      if(empty($user) || !hash_equals($json->password,$user->password())) {
         $r = new Response(422,"wrong credentials");
         $r->send();
            //$r->sendWithLog();
      }
      
       if(!empty($user)){
      // generate json web token
      $issued_at = time();
      $expiration_time = $issued_at + (60 * 60); // valid for 1 hour

      $token = array(
         "iat" => $issued_at,
         "exp" => $expiration_time,
         "iss" => JWT_ISSUER,
         "data" => array(
            "id" => $user->id()
         )
      );

      $jwt = JWT::encode( $token, JWT_BACKEND_KEY );
      $jsonResult = json_encode(
            array(
               "jwt_token" => $jwt,
               "data" => $user->USER_ROLE,
               "status" => $user->STATUT
            )
      );

      return Response::okResponse($jsonResult);
    }}
}