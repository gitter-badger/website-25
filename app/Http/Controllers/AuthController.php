<?php

namespace App\Http\Controllers;

use Auth;
use App\Wear;
use App\Skills;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['username', 'password']);

        $token = auth()->guard('api')->attempt($credentials);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $player = auth()->user();

        $player = [
            'x' => $player['x'],
            'y' => $player['y'],
            'username' => $player['username'],
            'uuid' => $player['uuid'],
            'level' => $player['level'],
            'online' => true,
            'sign_in' => time(),
            'hp' => [
                'current' => $player['hp_current'],
                'max' => $player['hp_max']
            ]
        ];

        $getSkills = Skills::find(auth()->id());

        $skillList = ['attack', 'defence', 'mining', 'smithing', 'fishing', 'cooking'];

        $skills = [];

        foreach($skillList as $skill) {
            $skills[] = [
                'level' => $getSkills[$skill . '_level'],
                'exp' => $getSkills[$skill . '_experience'],
                'name' => ucfirst($skill),
            ];
        }

        $player = array_merge($player, ['skills' => $skills]);

        $getWear = Wear::find(auth()->id());

        $wear = [
          'head' => $getWear['head'],
          'back' => $getWear['back'],
          'necklace' => $getWear['necklace'],
          'arrows' => [
            'quantity' => $getWear['arrows_qty'],
            'id' => $getWear['arrows_id']
          ],
          'right_hand' => $getWear['right_hand'],
          'armor' => $getWear['armor'],
          'left_hand' => $getWear['left_hand'],
          'gloves' => $getWear['gloves'],
          'feet' => $getWear['feet'],
          'ring' => $getWear['ring']
        ];

        $player = array_merge($player, ['wear' => $wear]);

        return response()->json($player);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->guard('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
