<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class MiddlewareController extends Controller
{
    public static function notificacionSesionDuplicada($user_id){
        try {

            $user = User::findOrFail($user_id);
            
            $params = array(
                'token' => config('parametros.TOKEN_WHATSAPP'),
                'to' => $user->phone,
                'body' => 'Se detecto un inicio de sesión en otro dispositivo, por razones de seguridad fueron cerradas ambas sesiones. Si estaba ejecutando alguna accion dentro del sistema no te preocupes, solo debe volver a Loguearse. Si esto persiste por favor comunicarse con el administrador del Sistema.'
            );
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => config('parametros.CURLOPT_URL_WHATSAPP'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($params),
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/x-www-form-urlencoded"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
            
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
        
    }
}