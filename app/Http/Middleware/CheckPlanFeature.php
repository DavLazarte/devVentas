<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Local;

class CheckPlanFeature
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('plan.feature:pos')
     * 
     * @param string $feature The feature to check: pos, clientes, caja, creditos, reportes
     */
    public function handle(Request $request, Closure $next, string $feature)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Admin siempre tiene acceso total
        if ($user->role_id == 1) {
            return $next($request);
        }

        $local = Local::where('id_user', $user->id)->first();

        if (!$local) {
            return response()->json([
                'message' => 'No tenés un local asociado',
                'plan_required' => true
            ], 403);
        }

        if (!$local->planAllows($feature)) {
            $plan = $local->planInfo;
            return response()->json([
                'message' => 'Tu plan actual no incluye esta función',
                'plan_required' => true,
                'current_plan' => $plan ? $plan->nombre : 'Sin plan',
                'feature' => $feature,
                'upgrade_needed' => true
            ], 403);
        }

        return $next($request);
    }
}
