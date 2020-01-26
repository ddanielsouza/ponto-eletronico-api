<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Storage;


class UserController extends Controller
{

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', 'like', $request->input('email'))->first();

        try {

            if ($user != null) {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json(['error' => 'invalid_credentials'], 400);
                }
            } else {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $token = JWTAuth::customClaims([
            'group' => $user->privilegio->alias
        ])->fromUser($user);

        return response()->json(compact('token'));
    }

    public function register(Request $request)
    {       
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'telefone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json((array) $validator->errors()->messages(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'telefone' => $request->get('telefone'),
            'password' => Hash::make($request->get('password')),
        ]);

        return response()->json(compact('user'), 201);
    }

    public function update(Request $request, $idUser)
    {       
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'telefone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json((array) $validator->errors()->messages(), 400);
        }

        $user = User::where('id', $idUser)->update([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'telefone' => $request->get('telefone'),
        ]);

        return response()->json(compact('user'), 201);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode(), 401);
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode(), 401);
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode(), 401);
        }
        $arrUser  =  $user->toArray();
        $arrUser['privilegio'] = [$user->privilegio->alias];
        return ['success' => true, 'data' => $arrUser];
    }

    public function refresh()
    {
        $token = JWTAuth::getToken();
        if (!$token) {
            throw new BadRequestHtttpException('Token not provided');
        }
        try {
            $token = JWTAuth::refresh($token);
        } catch (TokenInvalidException $e) {
            throw new AccessDeniedHttpException('The token is invalid');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
        return response()->json(['token' => $token]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['success' => true, 'message' => 'Logout successful'], 200);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'error' => 'Failed to logout, please try again.'], 500);
        }
    }

    public function getUsers(){
        try{
            $users = User::with('privilegio')->get();
            return response()->json(['success' => true, 'data' =>$users ], 200);
        }catch(\Exception $e){
            return response()->json(['success' => false, 'error' => 'erro interno.'], 500);
        }
    }
}
