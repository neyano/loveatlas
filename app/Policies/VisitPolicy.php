<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    /**
     * 更新権限: 自分の訪問記録のみ
     */
    public function update(User $user, Visit $visit): bool
    {
        return $user->id === $visit->user_id;
    }

    /**
     * 削除権限: 自分の訪問記録のみ
     */
    public function delete(User $user, Visit $visit): bool
    {
        return $user->id === $visit->user_id;
    }
}
