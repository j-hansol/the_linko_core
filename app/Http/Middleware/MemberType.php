<?php

namespace App\Http\Middleware;

use App\Http\JsonResponses\Common\Message;
use App\Lib\MemberType as Type;
use Closure;
use Illuminate\Http\Request;

class MemberType
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param ...$types
     * @return mixed|void
     */
    public function handle(Request $request, Closure $next, ...$types) {
        $user = current_user();

        foreach($types as $type) {
            $member_type = match ($type) {
                'operator' => Type::TYPE_OPERATOR,
                'intermediary' => Type::TYPE_INTERMEDIARY,
                'government' => Type::TYPE_GOVERNMENT,
                'order' => Type::TYPE_ORDER,
                'partner' => Type::TYPE_PARTNER,
                'attorney' => Type::TYPE_ATTORNEY,
                'manager' => Type::TYPE_MANAGER,
                'manager_operator' => Type::TYPE_MANAGER_OPERATOR,
                'company' => Type::TYPE_COMPANY,
                'foreign_government' => Type::TYPE_FOREIGN_GOVERNMENT,
                'foreign_manager_operator' => Type::TYPE_FOREIGN_MANAGER_OPERATOR,
                'foreign_manager' => Type::TYPE_FOREIGN_MANAGER,
                'recipient' => Type::TYPE_RECIPIENT,
                'foreign_partner' => Type::TYPE_FOREIGN_PARTNER,
                'foreign_person' => Type::TYPE_FOREIGN_PERSON,
                'developer' => Type::TYPE_DEVELOPER,
                'maintainer' => Type::TYPE_MAINTAINER,
                'premium' => Type::TYPE_PREMIUM,
                'partnership' => Type::TYPE_PARTNERSHIP
            };
            if($user->isOwnType($member_type))
                return $next($request);
        }

        if($request->wantsJson()) return new Message(403);
        else abort(401);
    }
}
