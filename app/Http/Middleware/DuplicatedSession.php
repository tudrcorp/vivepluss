<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Configuration;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Http\Controllers\MiddlewareController;
use Symfony\Component\HttpFoundation\Response;

class DuplicatedSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        $user = DB::table('sessions')->where('user_id', Auth::user()->id)->get();

        if(count($user) > 1 && Configuration::first()->duplicatedSession == 1){
            
            $user = DB::table('sessions')->where('user_id', Auth::user()->id)->delete();
            
            MiddlewareController::notificacionSesionDuplicada(Auth::id());
            
            //Retorno al Login de Filament
            return redirect()->to(Filament::getLoginUrl());

        }else{
            return $next($request);
        }
    }
}