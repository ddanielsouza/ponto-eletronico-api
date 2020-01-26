<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\BatidaPonto;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Storage;
use Facades\App\Services\BatidaPontoService;

class BatidaPontoController extends Controller
{
    public function registrarPonto(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $currentDate = new \DateTime();

            BatidaPonto::create([
                'user_id' => $user->id,
                'horaBatida' => $currentDate,
            ]);
            
            return response()->json(['success' => true], 200);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'error' => 'Failed to logout, please try again.'], 500);
        }
    }

    public function horasTrabalhadas(Request $request){
        try {
            $validator = \Validator::make($request->all(), [
                'dataInicio' => 'required|date',
                'dataFim' => 'required|date',
            ]);
    
            if ($validator->fails()) {
                return response()->json((array) $validator->errors()->messages(), 400);
            }

            $user = JWTAuth::parseToken()->authenticate();

            $dataInicio = new \DateTime($request->input('dataInicio'));
            $dataFim = new \DateTime($request->input('dataFim'));

            $batidas = BatidaPonto::where('user_id', $user->id)
                ->whereBetween('horaBatida', [
                    $dataInicio,
                    $dataFim
                ])
                ->orderBy('horaBatida', 'ASC')
                ->get();

            $horasTrabalhadas = BatidaPontoService::calcularHorasTrabalhadas($batidas, $dataInicio, $dataFim);
            
            return response()->json(['success' => true, 'data'=> $horasTrabalhadas], 200);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'error' => 'Failed to logout, please try again.'], 500);
        }
    }
}