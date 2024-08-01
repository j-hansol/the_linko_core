<?php

namespace App\Http\Middleware;

use App\Models\Contract;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckContractInvolved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
        $user = current_user();
        $file = $request->route()->parameter('file');
        $contract = $request->route()->parameter('contract');
        if($file) $contract_id = $file->contract_id;
        elseif($contract) $contract_id = $contract->id;
        else $contract_id = null;
        if(!$contract_id) abort(403);
        elseif($this->_isInvolved($user, $file->contract_id)) return $next($request);
        else abort(403);
    }

    /**
     * 계약관련 기관 여부를 판단한다.
     * @param User $user
     * @param int $contract_id
     * @return bool
     */
    private function _isInvolved(User $user, int $contract_id) : bool {
        $contract = Contract::find($contract_id);
        if($contract) {
            if($contract->order_user_id == $user->id
                || $contract->recipient_user_id == $user->id
                || $contract->provider_user_id == $user->id) return true;
            else {
                $order_manager_count = DB::table('order_managers')
                    ->where('contract_id', $contract_id)
                    ->where('manager_user_id', $user->id)
                    ->count();
                $recipient_manager_count = DB::table('recipient_managers')
                    ->where('contract_id', $contract_id)
                    ->where('manager_user_id', $user->id)
                    ->count();
                $company_count = DB::table('working_companies')
                    ->where('contract_id', $contract_id)
                    ->where('company_user_id', $user->id)
                    ->count();

                if($order_manager_count > 0 || $recipient_manager_count > 0 || $company_count > 0 ) return true;
                else return false;
            }
        }
        else return false;
    }
}
