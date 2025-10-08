<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderNotificationController extends Controller
{
    public function checkNewOrders(Request $request)
    {
       
        try {
            \Log::info('Check new orders calledtototo', [
                'user' => Auth::id(),
                'headers' => $request->headers->all()
            ]);
        
            $user = Auth::user();
            
            if (!$user) {
                \Log::error('No user authenticated');
                return response()->json(['error' => 'No autenticado'], 401);
            }
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            
            $local = $user->local;
            
            if (!$local) {
                return response()->json(['error' => 'Sin local'], 403);
            }
            
            $newOrders = Pedido::where('id_local', $local->id)
                ->where('created_at', '>', now()->subMinutes(5))
                ->where('estado', 'pendiente')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'hasNewOrders' => $newOrders->count() > 0,
                'count' => $newOrders->count(),
                'orders' => $newOrders->map(fn($o) => [
                    'id' => $o->id,
                    'cliente' => $o->nombre_cliente,
                    'total' => $o->total,
                    'tipo' => $o->tipo_pedido,
                    'created_at' => $o->created_at->diffForHumans()
                ]),
                'timestamp' => now()->timestamp
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}